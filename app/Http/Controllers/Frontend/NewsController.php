<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Language;
use App\Helpers\{ContentHelper};

class NewsController extends Controller
{
    /**
     * 消息列表與分類篩選
     *
     * @param Request $request 包含語系 (lang_id) 等請求資訊
     * @param int|null $category 透過路由傳入的分類 ID
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $category = null)
    {
        // 優先抓取 URL 路徑上的分類 ID，若無則抓 Query String
        $categoryId = $category ?: $request->input('category');

        // 語系防呆：確保一定有語系編號，若無則抓預設值
        $langId = $request->input('lang_id') ?: (Language::where('enabled', 1)->value('lang_id') ?: 1);

        // 取得分類清單：僅顯示狀態為「開啟」的分類
        $catList = NewsCategory::with(['descs' => function ($q) use ($langId) {
            $q->where('lang_id', $langId);
        }])->where('is_visible', 1)->orderByDesc('display_order')->get();

        // 資料本身要顯示 (is_visible = 1)
        // 並增加關鍵邏輯：使用 whereHas 檢查「所屬分類」也必須是 is_visible = 1
        $query = News::with(['descs', 'category.descs'])
            ->where('is_visible', 1)
            ->whereHas('category', function ($q) {
                $q->where('is_visible', 1); // 這是你要的「分類沒開，消息就不出」的關鍵防呆
            });

        // 如果使用者點選了特定分類，增加分類篩選條件
        $currentCategory = null;
        if ($categoryId) {
            $query->where('cat_id', $categoryId);
            // 從已過濾的分類清單中找到當前分類，用於頁面標題或麵包屑
            $currentCategory = $catList->find($categoryId);

            // 防呆：如果分類 ID 存在但該分類是被隱藏的 (不在 catList 內)，直接報 404 或跳回首頁
            if (!$currentCategory) {
                return redirect()->route('news.index');
            }
        }

        // 執行分頁查詢：先按排序欄位，再按時間排序
        $newsList = $query->orderByDesc('display_order')
                          ->orderByDesc('created_at')
                          ->paginate(9);

        // 麵包屑設定
        $crumbs = [['text' => '最新消息', 'href' => route('news.index')]];
        if ($currentCategory) {
            $catDesc = $currentCategory->descs->firstWhere('lang_id', $langId) ?? $currentCategory->descs->first();
            $crumbs[] = ['text' => $catDesc->name ?? '未分類', 'href' => null];
        }

        $this->setBreadcrumbs($crumbs);

        // 紀錄最後瀏覽的列表 URL，方便內頁點擊「回列表」時回到原本的分頁
        $request->session()->put('last_news_list_url', $request->fullUrl());

        return view('frontend.news.index', compact('newsList', 'catList', 'langId', 'categoryId'));
    }

    /**
     * 消息內頁顯示
     *
     * @param News $news 透過自動綁定取得的消息模型
     * @param Request $request 包含請求資訊的物件
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(News $news, Request $request)
    {
        // 安全防呆：檢查「消息本身」或「所屬分類」是否被隱藏
        if (!$news->is_visible || !$news->category || !$news->category->is_visible) {
            return redirect()->route('news.index');
        }

        $langId = $request->input('lang_id') ?: (Language::where('enabled', 1)->value('lang_id') ?: 1);
        $news->load(['descs', 'category.descs']);

        $desc = $news->descs->firstWhere('lang_id', $langId) ?: $news->descs->first();
        // Summernote 內容解析：將資料庫中的圖片相對路徑轉為絕對網址
        $desc->content = ContentHelper::decodeSiteUrl($desc->content);

        $catDesc = $news->category->descs->firstWhere('lang_id', $langId) ?? $news->category->descs->first();

        // 【修正核心】：將原先的 news.index 改為呼叫 news.category，並精確帶入分類 ID
        // 這樣 Laravel 才會根據路由設定，將網址漂亮地組合為 /news/category/3
        $this->setBreadcrumbs([
            ['text' => '最新消息', 'href' => route('news.index')],
            ['text' => $catDesc->name ?? '未分類', 'href' => route('news.category', ['category' => $news->cat_id])],
            ['text' => $desc->title, 'href' => null],
        ]);

        // 取得相鄰文章：同樣要確保抓到的前一則或後一則，其分類必須是開啟狀態
        $prevNews = News::where('is_visible', 1)
            ->whereHas('category', function($q) { $q->where('is_visible', 1); })
            ->where('created_at', '<', $news->created_at)
            ->orderByDesc('created_at')
            ->first();

        $nextNews = News::where('is_visible', 1)
            ->whereHas('category', function($q) { $q->where('is_visible', 1); })
            ->where('created_at', '>', $news->created_at)
            ->orderBy('created_at')
            ->first();

        return view('frontend.news.show', compact('news', 'desc', 'prevNews', 'nextNews', 'langId'));
    }
}
