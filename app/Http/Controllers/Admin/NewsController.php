<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsDesc;
use App\Models\NewsCategory;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Helpers\ContentHelper;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Support\Facades\Storage;

class NewsController extends BaseAdminController
{
    protected $pageTitle = '最新消息';

    // 列表：載入 news 主表與所有 desc（可在 view 選語系顯示）
    public function index(Request $request)
    {
        // 加入每頁筆數參數，預設 8
        $perPage = $request->input('per_page', 8);
        // 獲取搜尋關鍵字
        $search = $request->input('search');

        // 資料查詢與關聯載入
        $newsList = News::with(['descs', 'category'])
            ->orderBy('display_order', 'desc')
            ->orderBy('news_id', 'desc');

        // 如果有搜尋關鍵字，則加入篩選條件
        if ($search) {
            $newsList->whereHas('descs', function ($query) use ($search) {
                // 搜尋 NewsDesc 表中的 title 欄位
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        // 套用每頁筆數和分頁
        $newsList = $newsList->paginate($perPage);

        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        // 將搜尋關鍵字也傳遞給視圖，以便在搜尋框中保留
        return $this->view('admin.news.index', compact('newsList', 'langs', 'search'));
    }

    // 新增表單：需要分類與語系清單
    public function create()
    {
        $cats = NewsCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        return $this->view('admin.news.form', compact('cats', 'langs'));
    }

    // 儲存
    public function store(Request $request)
    {
        // ===============================
        // 1️⃣ 後端資料驗證
        // ===============================
        // 'required' 保證必填，避免 SQL 發生 NOT NULL 錯誤
        // 'exists:news_category,cat_id' 確認選的分類存在於 news_category
        $request->validate([
            'cat_id' => 'required|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096', // 4MB
            // desc 輸入驗證
            'desc' => 'nullable|array',
            'desc.*.title' => 'required_with:desc.*|string|max:255',
            'desc.*.content' => 'nullable|string',
        ]);

        // ===============================
        // 2️⃣ 圖片處理
        // ===============================
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $saveDir = 'news'; // 儲存子目錄

            try {
                $processedImage = ImageHelper::processImage($file, 600, 400, 'center_crop');
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename;
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                $imagePath = $fullPath;
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', '圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        // ===============================
        // 3️⃣ 建立 news 主表
        // ===============================
        $news = News::create([
            'cat_id' => $request->cat_id, // 必填
            'is_visible' => $request->is_visible ?? true,
            'display_order' => $request->display_order ?? 0,
            'image' => $imagePath,
        ]);

        // ===============================
        // 4️⃣ 建立 desc
        // ===============================
        if ($request->has('desc') && is_array($request->desc)) {
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

        // ===============================
        // 5️⃣ 回傳訊息
        // ===============================
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
    }


    // 編輯表單
    public function edit(News $news)
    {
        $cats = NewsCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $news->load('descs');
        $isEdit = $news->exists;

        // 轉成以 lang_id 為 key 的陣列，便於 blade 填值
        $descMap = [];
        foreach ($news->descs as $desc) {
            $desc->content = ContentHelper::decodeSiteUrl($desc->content);
            $descMap[$desc->lang_id] = $desc;
        }
        return $this->view('admin.news.form', compact('news', 'isEdit', 'cats', 'langs', 'descMap'));
    }

    // 更新
    public function update(Request $request, News $news)
    {
        $request->validate([
            'cat_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // 圖片處理
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $saveDir = 'news';

                try {
                    ImageHelper::deleteImage($news->image, 'public');
                    $processedImage = ImageHelper::processImage($file, 600, 400, 'center_crop');
                    $filename = ImageHelper::generateUniqueFilename($file);
                    $fullPath = $saveDir . '/' . $filename;
                    ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                    $news->image = $fullPath;
                } catch (\Throwable $e) {
                    return redirect()->back()->withInput()->with('error', '圖片處理失敗: ' . $e->getMessage());
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
                        DB::table('news_desc')->where('news_id', $news->news_id)->where('lang_id', $lang_id)->delete();
                        continue;
                    }

                    DB::table('news_desc')->updateOrInsert(
                        ['news_id' => $news->news_id, 'lang_id' => $lang_id],
                        [
                            'title' => $desc['title'],
                            'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                            'updated_at' => now(),
                            'created_at' => now(),
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
            ContentHelper::showMsg(
                1, // 1 = 錯誤訊息
                '更新失敗：' . $e->getMessage(),
                [
                    ['text' => '返回編輯', 'href' => route('admin.news.edit', $news->news_id)],
                    ['text' => '返回列表', 'href' => route('admin.news.index')],
                ],
                false // 失敗不自動跳轉，讓使用者選擇
            );

            return redirect()->back()->withInput();
        }
    }


    // 刪除
    public function destroy(News $news)
    {
        // 刪除翻譯
        NewsDesc::where('news_id', $news->news_id)->delete();

        // // 只有有圖片才刪除圖片
        if (!empty($news->image)) {
            ImageHelper::deleteImage($news->image, 'public');
        }

        $news->delete();

        // 修改：重定向並帶上 'form_success_swal' session 訊息，以便前端 SweetAlert2 捕獲
        return redirect()->route('admin.news.index')->with('form_success_swal', '消息已刪除');
    }
}
