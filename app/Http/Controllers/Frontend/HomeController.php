<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\{AdvertCategory, News};
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** * 取得指定代碼的廣告列表
     * @param string $code 分類代碼
     * @return \Illuminate\Support\Collection
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

    /** 首頁 */
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

        // 處理麵包屑與視圖返回
        $this->setBreadcrumbs([]);

        return view('frontend.layouts.home', compact('banners', 'features', 'homeNews'));
    }
}
