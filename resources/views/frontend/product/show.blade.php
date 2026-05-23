@extends('frontend.layouts.app')

@section('title', $product->descs->first()->title ?? '商品詳情')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.1.0/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery-bundle.min.css">
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@section('content')
    {{--
      內頁全寬滿版面 Banner 區塊
      [建議圖片尺寸] 寬度 1920px，高度在 450px ~ 550px 之間。
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
        @include('components.frontend.breadcrumb')

        <div class="p-product-detail__main">

            {{-- 左側：Swiper 相簿區 --}}
            <div class="p-product-detail__visual">
                {{--
                  主圖大圖輪播區
                  [建議圖片尺寸] 1200px x 1200px 正方形高解析度商品圖（確保燈箱放大後細節清晰）。
                --}}
                <div class="swiper p-product-slider-main js-slider-main" id="lightgallery">
                    <div class="swiper-wrapper">
                        @php
                            $mainImage = $product->image_url ? asset($product->image_url) : asset('images/default-product.jpg');
                            $mockGallery = [$mainImage, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=500&h=500', 'https://images.unsplash.com/photo-1551024601-bec78aea704b?q=80&w=500&h=500'];
                        @endphp

                        @foreach ($mockGallery as $img)
                            <a class="swiper-slide" href="{{ $img }}" data-src="{{ $img }}">
                                <img src="{{ $img }}" alt="{{ $product->descs->first()->title ?? 'product image' }}">
                            </a>
                        @endforeach
                    </div>
                </div>

                {{--
                  下方輔助細節縮圖輪播
                  [建議圖片尺寸] 與上方主圖同步，由前端自動限制為 80px x 80px 正方形。
                --}}
                <div class="swiper p-product-slider-thumbs js-slider-thumbs">
                    <div class="swiper-wrapper">
                        @foreach ($mockGallery as $img)
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
                    <span class="detail-cat">分類編號: {{ $product->cat_id ?? '未分類' }}</span>
                    <h1 class="detail-title">{{ $product->descs->first()->title ?? '未命名商品' }}</h1>
                    <p class="detail-price">NT$ {{ number_format($product->price ?? 0) }}</p>

                    <div class="detail-description">
                        <p>{{ $product->descs->first()->description ?? '暫無商品簡介。' }}</p>
                    </div>

                    <div class="detail-actions">
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

        {{-- 詳細圖文介紹（後台 Summernote 富文本內容） --}}
        <section class="p-product-detail__article">
            <div class="article-header">
                <h2>Product Details</h2>
            </div>

            {{--
              Summernote 後台編輯器輸出區塊
              [建議圖片尺寸] 寬度建議在 1000px 至 1400px 之間，高度不限，CSS 已強制執行防爆版防呆。
            --}}
            <div class="editor-content-wrap">
                {!! $product->descs->first()->content ?? '<p>暫無詳細商品說明。</p>' !!}
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11.1.0/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/fullscreen/lg-fullscreen.min.js"></script>
    <script src="{{ asset('js/frontend/product.js') }}"></script>
@endpush
