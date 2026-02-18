<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\{News, NewsDesc, NewsCategory};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper, SummernoteImageHelper};

class NewsController extends BaseAdminController
{
    // 定義權限與標題
    protected $permissionName = 'news';
    protected $pageTitle = '最新消息';

    /**
     * 頁面相關配置
     */
    protected $pageCfg = [
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

        // 取得統一的分頁數
        $perPage = $this->getPerPage($request);

        $newsList = News::with(['descs', 'category'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('descs', fn($q) => $q->where('title', 'like', "%{$search}%"));
            })
            ->orderByDesc('display_order')
            ->orderByDesc('news_id')
            ->paginate($perPage);

        $langs = $this->getActiveLanguages();

        return $this->view('admin.news.index', compact('newsList', 'langs', 'search'));
    }

    public function create()
    {
        // 👇 打開頁面時，先大掃除上次可能遺留的廢棄圖片
        SummernoteImageHelper::cleanAbandonedImages();

        return $this->renderForm(new News());
    }

    public function store(NewsRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $news = new News();

                // 填充資料
                $news->fill($request->safe()->except(['image_url']));

                // 處理檔案上傳 (ImageHelper 已優化安全性)
                $this->handleFileUploads($request, $news);

                $news->is_visible = $request->has('is_visible');
                $news->save();
                // 👇 儲存成功！將「觀察名單」清空，保護這些正式被啟用的圖片
                SummernoteImageHelper::commitTempImages();

                // 儲存多語系
                $this->saveTranslations($news, $request->desc);

                // 紀錄紀錄
                $news->writeLog('新增', $news->desc->title ?? '未知名消息');

                // 成功回傳
                ContentHelper::showMsg(0, '新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.news.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ]);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
            }
        });
    }

    public function edit(News $news)
    {
        // 👇 編輯頁面也順手清一下
        SummernoteImageHelper::cleanAbandonedImages();

        return $this->renderForm($news);
    }

    public function update(NewsRequest $request, News $news)
    {
        return DB::transaction(function () use ($request, $news) {
            try {
                $news->fill($request->safe()->except(['image_url']));

                // 處理更新 (會自動清理舊圖)
                $this->handleFileUploads($request, $news);

                // 更新主表
                $news->is_visible = $request->has('is_visible');
                $news->save();

                // 👇 儲存成功！將「觀察名單」清空，保護這些正式被啟用的圖片
                SummernoteImageHelper::commitTempImages();

                // 更新多語系資料
                $this->saveTranslations($news, $request->desc);

                $news->writeLog('編輯', $news->desc->title ?? '未知名消息');

                ContentHelper::showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ]);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    /**
     * 刪除消息 (優化寫法)
     */
    public function destroy(News $news)
    {
        $news->load('desc');
        $title = $news->desc->title ?? '未知名消息';

        // 注意：這裡不再需要手動用 ImageHelper::deleteImage 了！
        // 因為 News Model 掛載了 HasImageFields Trait，
        // 只要執行 delete()，Trait 會自動根據 $imageFields 屬性清理檔案。

        $news->delete();

        $news->writeLog('刪除', $title);

        return redirect()->route('admin.news.index')->with('form_success_swal', '消息已刪除');
    }

    /**
     * 單獨刪除圖片欄位 (AJAX)
     */
    public function deleteImageField(Request $request, News $news)
    {
        // 直接調用優化後的 Trait 方法
        return $news->deleteImageFieldGeneric($request);
    }

    /* --- 內部輔助方法 --- */

    private function renderForm(News $news)
    {
        $isEdit = (bool)$news->exists;
        $categories = NewsCategory::with('descs')->where('is_visible', 1)->orderByDesc('display_order')->get();
        $langs = $this->getActiveLanguages();
        $fileConfigs = $this->pageCfg['files'];

        $descMap = [];
        if ($isEdit) {
            $news->load('descs');
            foreach ($news->descs as $desc) {
                // 還原內容中的動態網址
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.news.form', compact('news', 'isEdit', 'categories', 'langs', 'descMap', 'fileConfigs'));
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
            NewsDesc::updateOrInsert(
                ['news_id' => $news->news_id, 'lang_id' => $langId],
                [
                    'title'       => $data['title'],
                    'description' => $data['description'] ?? null,
                    // Summernote 內容需編碼網址，避免換網域時圖片破圖
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
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
            // 同理，這也會觸發 Trait 的自動刪除檔案邏輯
            $news->delete();
        }

        $this->writeBatchDeleteLog('消息管理', $newsList->count(), $ids);

        return back()->with('form_success_swal', "已刪除 {$newsList->count()} 筆消息");
    }
}
