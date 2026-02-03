<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{News, NewsDesc, NewsCategory, ActionLog};
use Illuminate\Support\Facades\{DB, Log, Auth};
use App\Helpers\{ContentHelper, ImageHelper};

class NewsController extends BaseAdminController
{
    // 定義這個 Controller 屬於哪組權限
    protected $permissionName = 'news';
    protected $pageTitle = '最新消息';

    // 設定圖片配置，方便未來擴充
    protected $imageSizes = [
        'image_url' => [600, 400],
        // 'thumbnail' => [300, 200], // 縮圖範例
        // 'banner' => [1200, 500],   // Banner 範例
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');

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
        $this->validateRequest($request);

        return DB::transaction(function () use ($request) {
            try {
                // 1. 處理圖片並建立主表
                $news = new News();
                $this->handleImageUpload($request, $news);

                $news->fill([
                    'cat_id'        => $request->cat_id,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ])->save();

                // 2. 儲存多語系資料
                $this->saveTranslations($news, $request->desc);

                $news->writeLog('新增', $news->desc->title ?? '未知名消息');

                // 回傳訊息
                ContentHelper::showMsg(
                    0,
                    '新增完成',
                    [
                        ['text' => '繼續新增', 'href' => route('admin.news.create')],
                        ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                        ['text' => '返回列表', 'href' => route('admin.news.index')],
                    ],
                    true
                );

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗');
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
                // 1. 更新圖片與主表
                $this->handleImageUpload($request, $news);

                $news->update([
                    'cat_id'        => $request->cat_id,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ]);

                // 2. 更新多語系資料
                $this->saveTranslations($news, $request->desc);

                $news->writeLog('編輯', $news->desc->title ?? '未知名消息');

                // 回傳訊息
                ContentHelper::showMsg(
                    0,
                    '編輯操作完成',
                    [
                        ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                        ['text' => '返回列表', 'href' => route('admin.news.index')],
                    ],
                    true
                );

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("News Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    /**
     * 刪除表單
     */
    public function destroy(News $news)
    {
        // 刪除（刪之前抓）
        $news->load('desc');
        $news->writeLog('刪除', $news->desc->title ?? '未知名消息');

        // 刪除翻譯
        NewsDesc::where('news_id', $news->news_id)->delete();

        // 刪除所有圖片
        foreach ($this->imageSizes as $inputName => $_) {
            if (!empty($news->$inputName)) {
                ImageHelper::deleteImage($news->$inputName, 'public');
            }
        }

        $news->delete();


        // 修改：重定向並帶上 'form_success_swal' session 訊息，以便前端 SweetAlert2 捕獲
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
        $imageSizes = $this->imageSizes;

        $descMap = [];
        if ($isEdit) {
            $news->load('descs');
            foreach ($news->descs as $desc) {
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.news.form', compact('news', 'isEdit', 'categories', 'langs', 'descMap', 'imageSizes'));
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
            'image_url'              => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc'               => 'nullable|array',
            'desc.*.title'       => 'required_with:desc.*|string|max:255',
            'desc.*.description' => 'nullable|string|max:255',
            'desc.*.content'     => 'nullable|string',
        ]);
    }

    /**
     * 統一處理圖片上傳與舊圖刪除
     */
    private function handleImageUpload(Request $request, News $news)
    {
        foreach ($this->imageSizes as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                if ($news->$field) ImageHelper::deleteImage($news->$field, 'public');

                $file = $request->file($field);
                $processed = ImageHelper::processImage($file, $width, $height, 'center_crop');
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = "news/{$filename}";

                ImageHelper::saveProcessedImage($processed, $fullPath, 'public', 90, 'jpeg');
                $news->$field = $fullPath;
            }
        }
    }

    /**
     * 儲存/更新多語系描述
     */
    private function saveTranslations(News $news, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            if (empty($data['title'])) {
                NewsDesc::where('news_id', $news->news_id)->where('lang_id', $langId)->delete();
                continue;
            }

            DB::table('news_desc')->updateOrInsert(
                ['news_id' => $news->news_id, 'lang_id' => $langId],
                [
                    'title'       => $data['title'],
                    'description' => $data['description'] ?? null,
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    /**
     * 批次刪除（依勾選）
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', '請選擇要刪除的消息');

        $newsList = News::whereIn('news_id', $ids)->get();

        News::withoutEvents(function () use ($newsList) {
            foreach ($newsList as $news) {
                foreach (array_keys($this->imageSizes) as $field) {
                    if ($news->$field) ImageHelper::deleteImage($news->$field, 'public');
                }
                $news->descs()->delete();
                $news->delete(); // 不會觸發 deleted 事件
            }
        });

        // 寫 Batch Log
        $this->writeBatchDeleteLog('消息管理', $newsList->count(), $ids);

        return back()->with('form_success_swal', "已刪除 {$newsList->count()} 筆消息");
    }
}
