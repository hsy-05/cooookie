<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Language;
use App\Helpers\{ContentHelper};

class ProductController extends Controller
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
        $catList = ProductCategory::with(['descs' => function ($q) use ($langId) {
            $q->where('lang_id', $langId);
        }])->where('is_visible', 1)->orderByDesc('display_order')->get();

        // 建立查詢基底：最新消息本身要顯示 (is_visible = 1)
        // 並增加關鍵邏輯：使用 whereHas 檢查「所屬分類」也必須是 is_visible = 1
        $query = Product::with(['descs', 'category.descs'])
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
                return redirect()->route('product.index');
            }
        }

        // 執行分頁查詢：先按排序欄位，再按時間排序
        $items = $query->orderByDesc('display_order')
                          ->orderByDesc('created_at')
                          ->paginate(9);

        // 麵包屑設定
        $crumbs = [['text' => '最新消息', 'href' => route('product.index')]];
        if ($currentCategory) {
            $catDesc = $currentCategory->descs->firstWhere('lang_id', $langId) ?? $currentCategory->descs->first();
            $crumbs[] = ['text' => $catDesc->name ?? '未分類', 'href' => null];
        }

        $this->setBreadcrumbs($crumbs);

        // 紀錄最後瀏覽的列表 URL，方便內頁點擊「回列表」時回到原本的分頁
        session(['last_product_list_url' => request()->fullUrl()]);

        return view('frontend.product.index', compact('items', 'catList', 'langId', 'categoryId'));
    }

    /**
     * 消息內頁顯示
     *
     * @param Product $product 透過自動綁定取得的消息模型
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Product $product, Request $request)
    {
        // 安全防呆：檢查「消息本身」或「所屬分類」是否被隱藏
        // 如果其中一個隱藏，即便有直接連結也不給看
        if (!$product->is_visible || !$product->category || !$product->category->is_visible) {
            return redirect()->route('product.index');
        }

        $langId = $request->input('lang_id') ?: (Language::where('enabled', 1)->value('lang_id') ?: 1);
        $product->load(['descs', 'category.descs']);

        $desc = $product->descs->firstWhere('lang_id', $langId) ?: $product->descs->first();
        // Summernote 內容解析：將資料庫中的圖片相對路徑轉為絕對網址
        $desc->content = ContentHelper::decodeSiteUrl($desc->content);

        $catDesc = $product->category->descs->firstWhere('lang_id', $langId) ?? $product->category->descs->first();

        $this->setBreadcrumbs([
            ['text' => '最新消息', 'href' => route('product.index')],
            ['text' => $catDesc->name ?? '未分類', 'href' => route('product.index', ['category' => $product->cat_id])],
            ['text' => $desc->title, 'href' => null],
        ]);

        // 取得相鄰文章：同樣要確保抓到的前一則或後一則，其分類必須是開啟狀態
        $prevProduct = Product::where('is_visible', 1)
            ->whereHas('category', function($q) { $q->where('is_visible', 1); })
            ->where('created_at', '<', $product->created_at)
            ->orderByDesc('created_at')
            ->first();

        $nextProduct = Product::where('is_visible', 1)
            ->whereHas('category', function($q) { $q->where('is_visible', 1); })
            ->where('created_at', '>', $product->created_at)
            ->orderBy('created_at')
            ->first();

        return view('frontend.product.show', compact('product', 'desc', 'prevProduct', 'nextProduct', 'langId'));
    }
}
