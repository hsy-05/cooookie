<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Models\{News, NewsDesc, NewsCategory, Language};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class NewsController extends BaseAdminController
{
    // 設定權限名稱，自動綁定 news.view, news.create, news.delete
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

    public function destroy(News $news)
    {
        DB::transaction(function () use ($news) {
            // 刪除圖片檔案
            foreach (array_keys($this->imageSizes) as $field) {
                if ($news->$field) ImageHelper::deleteImage($news->$field, 'public');
            }
            // 刪除關聯與主體 (假設資料庫有設定 cascade 可簡化，否則手動刪除 desc)
            $news->descs()->delete();
            $news->delete();
        });

        return back()->with('form_success_swal', '刪除成功');
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

}
