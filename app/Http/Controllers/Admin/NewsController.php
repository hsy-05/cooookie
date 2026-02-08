<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{News, NewsDesc, NewsCategory, ActionLog};
use Illuminate\Support\Facades\{DB, Log, Auth};
use App\Helpers\{ContentHelper, ImageHelper};

class NewsController extends BaseAdminController
{
    // 定義這個 Controller 屬於哪組權限與標題
    protected $permissionName = 'news';
    protected $pageTitle = '最新消息';

    /**
     * 頁面相關配置
     */
    protected $pageCfg = [
        // 定義哪些欄位需要處理檔案上傳
        'files' => [
            'image_url' => [
                'path'   => 'news',     // 儲存路徑
                'width'  => 600,               // 寬度 (若不縮圖可設為 null)
                'height' => 400,               // 高度
                'mode'   => 'center_crop',     // 處理模式：center_crop, scale_fit
                'useOriginalName' => false,    // 是否使用原檔名 (false 代表自動生成唯一名稱)
            ],
            // 未來若有 PDF 或 縮圖，直接在這裡增加一組設定即可
        ],
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');

        // 使用 Eager Loading (with) 減少資料庫查詢壓力 (解決 N+1 問題)
        $newsList = News::with(['descs', 'category'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('descs', fn($q) => $q->where('title', 'like', "%{$search}%"));
            })
            ->orderByDesc('display_order')
            ->orderByDesc('news_id')
            ->paginate($request->input('per_page', 8));

        $langs = $this->getActiveLanguages();

        return $this->view('admin.news.index', compact('newsList', 'langs', 'search'));
    }

    public function create()
    {
        return $this->renderForm(new News());
    }

    public function store(Request $request)
    {
        // 基礎驗證
        $this->validateRequest($request);

        return DB::transaction(function () use ($request) {
            try {
                $news = new News();

                // 處理檔案/圖片上傳
                $this->handleFileUploads($request, $news);

                // 儲存主表資料
                $news->fill([
                    'cat_id'        => $request->cat_id,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ])->save();

                // 儲存多語系資料
                $this->saveTranslations($news, $request->desc);

                // 紀錄操作日誌
                $news->writeLog('新增', $news->desc->title ?? '未知名消息');

                // 成功回傳 (使用自定義 ContentHelper)
                ContentHelper::showMsg(0, '新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.news.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
            }
        });
    }

    public function edit(News $news)
    {
        return $this->renderForm($news);
    }

    public function update(Request $request, News $news)
    {
        $this->validateRequest($request);

        return DB::transaction(function () use ($request, $news) {
            try {
                // 處理檔案/圖片更新 (會自動判斷舊檔並刪除)
                $this->handleFileUploads($request, $news);

                // 更新主表
                $news->update([
                    'cat_id'        => $request->cat_id,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ]);

                // 更新多語系資料
                $this->saveTranslations($news, $request->desc);

                $news->writeLog('編輯', $news->desc->title ?? '未知名消息');

                ContentHelper::showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    public function destroy(News $news)
    {
        // 先抓取標題供 Log 使用
        $news->load('desc');
        $title = $news->desc->title ?? '未知名消息';

        // 刪除相關聯的所有實體檔案 (防呆：避免伺服器留下一堆廢圖)
        foreach (array_keys($this->pageCfg['files']) as $field) {
            if ($news->$field) {
                ImageHelper::deleteImage($news->$field, 'public');
            }
        }

        // 刪除資料庫紀錄
        NewsDesc::where('news_id', $news->news_id)->delete();
        $news->delete();

        $news->writeLog('刪除', $title);

        return redirect()->route('admin.news.index')->with('form_success_swal', '消息已刪除');
    }

    /* --- 內部輔助方法 (Private Helper Methods) --- */

    /**
     * 渲染表單通用邏輯
     */
    private function renderForm(News $news)
    {
        $isEdit = (bool)$news->exists;
        $categories = NewsCategory::with('descs')->where('is_visible', 1)->orderByDesc('display_order')->get();
        $langs = $this->getActiveLanguages();

        // 將配置傳給前端，以便顯示建議尺寸提示
        $fileConfigs = $this->pageCfg['files'];

        $descMap = [];
        if ($isEdit) {
            $news->load('descs');
            foreach ($news->descs as $desc) {
                // 解碼內容中的動態網址 (Summernote 用)
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.news.form', compact('news', 'isEdit', 'categories', 'langs', 'descMap', 'fileConfigs'));
    }

    /**
     * 驗證請求
     */
    private function validateRequest(Request $request)
    {
        $request->validate([
            'cat_id'             => 'required|exists:news_category,cat_id',
            'is_visible'         => 'nullable|boolean',
            'display_order'      => 'nullable|integer',
            'image_url'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'desc'               => 'nullable|array',
            'desc.*.title'       => 'required_with:desc.*|string|max:255',
            'desc.*.description' => 'nullable|string|max:255',
            'desc.*.content'     => 'nullable|string',
        ]);
    }

    /**
     * 萬用檔案上傳處理邏輯
     * 自動根據 $pageCfg 的設定，處理所有需要上傳的欄位
     */
    private function handleFileUploads(Request $request, News $news)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            // 如果 Request 裡有這個檔案，才進行處理
            if ($request->hasFile($field)) {
                $news->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $news->$field, // 傳入舊路徑以供刪除
                    $config
                );
            }
        }
    }

    /**
    * 刪除圖片欄位的通用方法
    * 前端會傳入要刪除的欄位名稱 (例如 image_url)，這樣這個方法就可以通用於多個圖片欄位
    */
    public function deleteImageField(Request $request, News $news)
    {
        // 調用 Trait 裡面的通用邏輯，傳入當前的 $news 模型實例
        // 並明確告訴 Trait 要刪除的欄位名稱 (從前端傳來，或直接寫死在控制器)
        return $this->deleteImageFieldGeneric($request, $news);
    }

    /**
     * 儲存/更新多語系描述
     */
    private function saveTranslations(News $news, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            // 防呆：如果標題是空的，就當作這語系沒資料，直接刪除
            if (empty($data['title'])) {
                NewsDesc::where('news_id', $news->news_id)->where('lang_id', $langId)->delete();
                continue;
            }

            // 使用 updateOrInsert 簡化邏輯 (有則更，無則增)
            DB::table('news_desc')->updateOrInsert(
                ['news_id' => $news->news_id, 'lang_id' => $langId],
                [
                    'title'       => $data['title'],
                    'description' => $data['description'] ?? null,
                    // Summernote 內容需編碼網址，避免換網域時圖片破圖
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    /**
     * 列表批次刪除
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', '請選擇要刪除的消息');

        $newsList = News::whereIn('news_id', $ids)->get();

        foreach ($newsList as $news) {
            // 批次刪除時也要確實清理檔案
            foreach (array_keys($this->pageCfg['files']) as $field) {
                if ($news->$field) ImageHelper::deleteImage($news->$field, 'public');
            }
            $news->descs()->delete();
            $news->delete();
        }

        $this->writeBatchDeleteLog('消息管理', $newsList->count(), $ids);

        return back()->with('form_success_swal', "已刪除 {$newsList->count()} 筆消息");
    }
}
