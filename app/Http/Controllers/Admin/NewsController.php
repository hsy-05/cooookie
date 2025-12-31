<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsDesc;
use App\Models\NewsCategory;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Helpers\ContentHelper;
use App\Helpers\ImageHelper;

class NewsController extends BaseAdminController
{
    protected $pageTitle = '最新消息';

    /**
     * 圖片欄位尺寸設定
     * key = input name
     * value = [width, height]
     */
    protected $imageSizes = [
        'image' => [600, 400],     // 封面圖片
        // 'thumbnail' => [300, 200], // 縮圖範例
        // 'banner' => [1200, 500],   // Banner 範例
    ];

    /**
     * 取得某個圖片欄位建議尺寸
     */
    protected function getImageSize(string $field): ?array
    {
        return $this->imageSizes[$field] ?? null;
    }

    /**
     * 傳給 Blade 所有圖片建議尺寸
     */
    protected function getAllImageSizes(): array
    {
        return $this->imageSizes;
    }

    /**
     * 列表
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 8);
        $search = $request->input('search');

        $newsList = News::with(['descs', 'category'])
            ->orderBy('display_order', 'desc')
            ->orderBy('news_id', 'desc');

        if ($search) {
            $newsList->whereHas('descs', function ($query) use ($search) {

                // 搜尋 NewsDesc 表中的 title 欄位
                $query->where('title', 'like', "%{$search}%");
            });
        }


        // 套用每頁筆數和分頁
        $newsList = $newsList->paginate($perPage);
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        return $this->view('admin.news.index', compact('newsList', 'langs', 'search'));
    }

    /**
     * 新增表單
     */
    public function create()
    {
        $categories = NewsCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $imageSizes = $this->getAllImageSizes();

        return $this->view('admin.news.form', compact('categories', 'langs', 'imageSizes'));
    }

    /**
     * 儲存表單
     */
    public function store(Request $request)
    {

        // 1️⃣ 後端驗證
        $request->validate([
            'cat_id' => 'required|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc' => 'nullable|array',
            'desc.*.title' => 'required_with:desc.*|string|max:255',
            'desc.*.content' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // 2️⃣ 圖片處理 (支援多欄位)
            $imagePaths = [];
            foreach ($this->imageSizes as $inputName => [$width, $height]) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $saveDir = 'news';
                    $processedImage = ImageHelper::processImage($file, $width, $height, 'center_crop');
                    $filename = ImageHelper::generateUniqueFilename($file);
                    $fullPath = $saveDir . '/' . $filename;
                    ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                    $imagePaths[$inputName] = $fullPath;
                }
            }

            // 3️⃣ 建立 news 主表
            $newsData = [
                'cat_id' => $request->cat_id,
                'is_visible' => $request->is_visible ?? true,
                'display_order' => $request->display_order ?? 0,
            ];
            foreach ($imagePaths as $field => $path) {
                $newsData[$field] = $path;
            }
            $news = News::create($newsData);

            // 4️⃣ 建立 desc (多語系)
            if ($request->filled('desc')) {
                foreach ($request->desc as $lang_id => $desc) {
                    if (!empty($desc['title'])) {
                        NewsDesc::create([
                            'news_id' => $news->news_id,
                            'lang_id' => $lang_id,
                            'title' => $desc['title'],
                            'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                        ]);
                    }
                }
            }

            DB::commit();

            // 5️⃣ 回傳訊息
            ContentHelper::showMsg(
                0,
                '消息新增完成',
                [
                    ['text' => '繼續新增', 'href' => route('admin.news.create')],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ],
                true
            );

            return redirect()->back();
        } catch (\Throwable $e) {
            // 發生錯誤時回滾交易
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', '新增失敗: ' . $e->getMessage());
        }
    }

        /**
         * 編輯表單
         */
    public function edit(News $news)
    {dd($news);
        $categories = NewsCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $news->load('descs');
        $isEdit = $news->exists;

        // 轉成以 lang_id 為 key 的陣列
        $descMap = [];
        foreach ($news->descs as $desc) {
            $desc->content = ContentHelper::decodeSiteUrl($desc->content);
            $descMap[$desc->lang_id] = $desc;
        }

        $imageSizes = $this->getAllImageSizes();

        return $this->view('admin.news.form', compact('news', 'isEdit', 'categories', 'langs', 'descMap', 'imageSizes'));
    }

    /**
     * 更新表單
     */
    public function update(Request $request, News $news)
    {
        $request->validate([
            'cat_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc' => 'nullable|array',
        ]);

        // 開始資料庫交易，保證資料一致性
        DB::beginTransaction();
        try {
            // 圖片處理 (支援多欄位)
            foreach ($this->imageSizes as $inputName => [$width, $height]) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $saveDir = 'news';

                    if (!empty($news->$inputName)) {
                        ImageHelper::deleteImage($news->$inputName, 'public');
                    }

                    $processedImage = ImageHelper::processImage($file, $width, $height, 'center_crop');
                    $filename = ImageHelper::generateUniqueFilename($file);
                    $fullPath = $saveDir . '/' . $filename;
                    ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                    $news->$inputName = $fullPath;
                }
            }

            // 更新主表
            $news->update([
                'cat_id' => $request->cat_id,
                'is_visible' => $request->is_visible ?? true,
                'display_order' => $request->display_order ?? 0,
            ]);

            // 更新 desc
            if ($request->filled('desc')) {
                foreach ($request->desc as $lang_id => $desc) {
                    if (empty($desc['title'])) {
                        NewsDesc::where('news_id', $news->news_id)->where('lang_id', $lang_id)->delete();
                        continue;
                    }
                    NewsDesc::updateOrCreate(
                        ['news_id' => $news->news_id, 'lang_id' => $lang_id],
                        [
                            'title' => $desc['title'],
                            'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            DB::commit();

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
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', '更新失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除表單
     */
    public function destroy(News $news)
    {
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
}
