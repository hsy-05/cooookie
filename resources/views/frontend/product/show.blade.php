@extends('frontend.layouts.app')

@section('title', $product['title'])

@push('styles')
    {{-- 第三方插件樣式 --}}
    {{-- Swiper 11.1.0 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.0/swiper-bundle.min.css">

    {{-- lightGallery 核心與所有插件的 Bundle CSS (包含 fullscreen 等樣式) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@php
$product = [
    'title' => '經典原味生乳捲',
    'category' => '經典手工',
    'price' => 880,
    'description' => '北海道乳源與法國奶油融合出的經典口感。',
    'content' => '
        <p>使用低溫熟成奶油與日本麵粉製作。</p>
        <p>蛋糕體濕潤細膩，入口柔軟。</p>
    ',
    'gallery' => [
        'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=500&h=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=500&h=500&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1551024601-bec78aea704b?q=80&w=500&h=500&auto=format&fit=crop',
    ]
];
@endphp
@section('content')
    {{--
      內頁全寬滿版面 Banner 區塊
      圖片尺寸建議：1920 x 450 px ~ 1920 x 550 px 之間 (可比列表頁略矮，讓視覺快速聚焦內文)
      重要規範：需注意半透明黑底或白字在上方的易讀性，背景圖片色彩不宜過度雜亂
    --}}
    <section class="page-banner">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1481391243133-f96216dcb5d2?q=80&w=1920&auto=format" alt="Products Banner" class="banner-img js-parallax-img">
        </div>
        <div class="c-banner-block">
            <span class="c-banner-subtitle">Our Collections</span>
            <h1 class="c-banner-title">甜點藝廊</h1>
        </div>
    </section>


    <div class="container-1600">
        {{-- 麵包屑 --}}
        @include('components.frontend.breadcrumb')

        <div class="p-product-detail__main">

            {{-- 左側：Swiper 相簿區 --}}
            <div class="p-product-detail__visual">

                {{--
                  主圖大圖輪播（點擊可觸發 LightGallery 燈箱）
                  圖片尺寸建議：1000 x 1000 px ~ 1200 x 1200 px (1:1 正方形)
                  重要規範：因為此區塊有放大燈箱功能，圖檔解析度需稍高。
                  建議統一使用 1200px 等比例正方形，既能完美配合 Swiper 的外層容器，放大時細節依然清晰
                --}}
                <div class="swiper p-product-slider-main js-slider-main" id="lightgallery">
                    <div class="swiper-wrapper">
                        @foreach ($product['gallery'] as $img)
                            <a class="swiper-slide" href="{{ $img }}" data-src="{{ $img }}">
                                <img src="{{ $img }}" alt="product image">
                            </a>
                        @endforeach
                    </div>
                </div>

                {{--
                  下方輔助細節縮圖輪播
                  圖片尺寸建議：直接同步使用上方大圖（由前端自動縮放），不需請後台重複上傳另一套尺寸
                  重要規範：CSS 處理時需固定縮圖容器寬高比例（通常為 1:1），並加上 opacity 或 border 作為當前選中狀態的識別
                --}}
                <div class="swiper p-product-slider-thumbs js-slider-thumbs">
                    <div class="swiper-wrapper">
                        @foreach ($product['gallery'] as $img)
                            <div class="swiper-slide">
                                <img src="{{ $img }}" alt="thumb">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 右側：產品資訊區 --}}
            <div class="p-product-detail__content">
                <div class="p-product-detail__sticky">
                    <span class="detail-cat">{{ $product['category'] }}</span>
                    <h1 class="detail-title">{{ $product['title'] }}</h1>
                    <p class="detail-price">NT$ {{ number_format($product['price']) }}</p>
                    <div class="detail-description">
                        <p>{{ $product['description'] }}</p>
                    </div>
                    <div class="detail-actions">
                        {{-- 模擬購物車按鈕，對應專業電商排版 --}}
                        <div class="qty-control">
                            <button class="qty-btn" data-qty="minus">-</button>
                            <input type="number" value="1" readonly>
                            <button class="qty-btn" data-qty="plus">+</button>
                        </div>
                        <button class="btn-buy">加入購物車</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 詳細圖文介紹 --}}
        <section class="p-product-detail__article">
            <div class="article-header">
                <h2>Product Details</h2>
            </div>

            {{--
              Summernote 後台編輯器輸出區塊
              圖片尺寸建議：寬度建議在 1000px 至 1400px 之間，高度隨意（直式橫式皆可）
              重要規範：考慮到 Summernote 後台由客戶自行上傳圖片，工程上必須在 CSS 內針對 .editor-content-wrap img
              強制設定 `max-width: 100% !important; height: auto !important;`，避免客戶丟原圖造成行動版破版
            --}}
            <div class="editor-content-wrap">
                {!! $product['content'] !!}
            </div>
        </section>
    </div>

@endsection

@push('scripts')
    {{-- 插件載入 --}}<!-- Swiper 11.1.0 JS (主要用於首頁輪播) -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11.1.0/swiper-bundle.min.js"></script>

<!-- LightGallery (燈箱功能主程式) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>

<!-- LightGallery Fullscreen Plugin (全螢幕插件) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.min.js"></script>
    <script src="{{ asset('js/frontend/product.js') }}"></script>
@endpush
