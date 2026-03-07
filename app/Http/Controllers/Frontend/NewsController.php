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
     * * @param Request $request 包含分類 ID (category) 與語系 (lang_id)
     * * @param int|null $category 透過路由傳入的分類 ID (由 Laravel 自動抓取網址上的數字)
     */
    public function index(Request $request, $category = null)
    {
        // 優先使用網址路徑上的 $category，如果沒有才看 Query String (相容舊寫法)
        $categoryId = $category ?: $request->input('category');

        // 防呆：如果沒傳語系，自動抓預設啟用的第一個語系
        $langId = $request->input('lang_id') ?: (Language::where('enabled', 1)->value('lang_id') ?: 1);

        // 預先載入分類及其對應語系描述，避免 N+1 效能問題
        $catList = NewsCategory::with(['descs' => function ($q) use ($langId) {
            $q->where('lang_id', $langId);
        }])->where('is_visible', 1)->orderByDesc('display_order')->get();

        $query = News::with(['descs', 'category.descs'])->where('is_visible', 1);

        // 分類篩選邏輯
        $currentCategory = null;
        if ($categoryId) {
            $query->where('cat_id', $categoryId);
            $currentCategory = $catList->find($categoryId);
        }

        // 分頁處理，每頁 9 筆符合網格佈局
        $newsList = $query->orderByDesc('display_order')->orderByDesc('created_at')->paginate(9);

        // --- 麵包屑與 Title 處理 ---
        $crumbs = [['text' => '最新消息', 'href' => route('news.index')]];

        if ($currentCategory) {
            $catDesc = $currentCategory->descs->firstWhere('lang_id', $langId) ?? $currentCategory->descs->first();
            $crumbs[] = ['text' => $catDesc->name ?? '未分類', 'href' => null];
        }

        // 呼叫父類別方法，自動處理全站共享變數
        $this->setBreadcrumbs($crumbs);

        session(['last_news_list_url' => request()->fullUrl()]);

        return view('frontend.news.index', compact('newsList', 'catList', 'langId', 'categoryId'));
    }

    /**
     * 消息內頁顯示
     * * @param News $news 透過 Route Model Binding 自動取得模型
     * @param Request $request
     */
    public function show(News $news, Request $request)
    {
        // 安全檢查：隱藏的消息不可直接存取
        if (!$news->is_visible) {
            return redirect()->route('news.index');
        }

        $langId = $request->input('lang_id') ?: (Language::where('enabled', 1)->value('lang_id') ?: 1);
        $news->load(['descs', 'category.descs']);

        // 取得正確語系的描述內容
        $desc = $news->descs->firstWhere('lang_id', $langId) ?: $news->descs->first();
        // Summernote 內容解析 (動態網址還原)
        $desc->content = ContentHelper::decodeSiteUrl($desc->content);

        // 取得分類名稱用於麵包屑
        $catDesc = $news->category->descs->firstWhere('lang_id', $langId) ?? $news->category->descs->first();

        // --- 麵包屑與 Title 處理 ---
        $this->setBreadcrumbs([
            ['text' => '最新消息', 'href' => route('news.index')],
            ['text' => $catDesc->name ?? '未分類', 'href' => route('news.index', ['category' => $news->cat_id])],
            ['text' => $desc->title, 'href' => null],
        ]);

        // 取得相鄰文章 (前一篇、後一篇)
        $prevNews = News::where('is_visible', 1)->where('created_at', '<', $news->created_at)->orderByDesc('created_at')->first();
        $nextNews = News::where('is_visible', 1)->where('created_at', '>', $news->created_at)->orderBy('created_at')->first();

        return view('frontend.news.show', compact('news', 'desc', 'prevNews', 'nextNews', 'langId'));
    }
}
