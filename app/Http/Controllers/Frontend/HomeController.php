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
        $idxBannerCate = AdvertCategory::where('cat_code', 'idx_banner')
            ->with(['descs', 'adverts' => function ($q) {
                $q->where('is_visible', 1)->orderBy('display_order', 'desc');
            }])
            ->first();

        // 若有廣告資料就取出
        $banners = $idxBannerCate ? $idxBannerCate->adverts : collect([]);


        // 撈取廣告分類為 idx_block2 的橫幅廣告
        $idxBlock2Cate = AdvertCategory::where('cat_code', 'idx_block2')
            ->with(['descs', 'adverts' => function ($q) {
                $q->where('is_visible', 1)->orderBy('display_order', 'desc');
            }])
            ->first();

        // 若有廣告資料就取出
        $features = $idxBlock2Cate ? $idxBlock2Cate->adverts : collect([]);


        return view('frontend.layouts.home', compact('banners', 'features'));
    }
}
