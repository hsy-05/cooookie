<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{AdvertCategory, News, ProductCategory}; // 引入產品分類模型
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * 取得指定代碼的廣告列表
     * @param string $code 分類代碼
     * @return \Illuminate\Support\Collection 廣告資料集合
     */
    private function getAdvertsByCode($code)
    {
        $category = AdvertCategory::where('cat_code', $code)
            ->with(['adverts' => function ($q) {
                $q->where('is_visible', 1)
                    ->with('currentDesc') // 確保抓到標題與副標題
                    ->orderBy('display_order', 'desc');
            }])
            ->first();

        return $category ? $category->adverts : collect([]);
    }

    /**
     * 首頁
     * @return \Illuminate\View\View 前台首頁視圖
     */
    public function index()
    {
        // 獲取橫幅廣告 (idx_banner)
        $banners = $this->getAdvertsByCode('idx_banner');

        // 獲取區塊2廣告 (idx_block2)
        $features = $this->getAdvertsByCode('idx_block2');

        // 獲取最新消息
        $homeNews = News::where('is_visible', 1)
            ->where('is_visible_home', 1)
            ->with('currentDesc')
            ->orderBy('display_order', 'desc')
            ->latest()
            ->get();

        // 獲取產品分類（真實後端資料）
        // 效能優化：使用 with 預先載入目前語系描述，避免 N+1 查詢問題
        // 防呆機制：只撈取後台設定為顯示(is_visible = 1)的分類
        $productCategories = ProductCategory::where('is_visible', 1)
            ->with(['currentDesc'])
            ->orderBy('display_order', 'desc')
            ->get();

        // 處理麵包屑與視圖返回
        $this->setBreadcrumbs([]);

        // 將撈出來的 $productCategories 變數傳遞給前端 Blade 樣板
        return view('frontend.layouts.home', compact('banners', 'features', 'homeNews', 'productCategories'));
    }
}
