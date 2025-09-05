<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AdvertCategory;
use App\Models\Advert;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** 首頁 */
    public function index()
    {
        // 撈取廣告分類為 idx_banner 的橫幅廣告
        $bannerCategory = AdvertCategory::where('cat_code', 'idx_banner')
            ->with(['descs', 'adverts' => function ($q) {
                $q->where('is_visible', 1)->orderBy('display_order', 'desc');
            }])
            ->first();

        // 若有廣告資料就取出
        $banners = $bannerCategory ? $bannerCategory->adverts : collect([]);

        return view('frontend.layouts.home', compact('banners'));
    }
}
