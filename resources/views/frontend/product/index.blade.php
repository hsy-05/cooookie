@extends('frontend.layouts.app')

@section('title', '精品甜點｜產品介紹')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@php
    $categories = [
        '全部商品',
        '經典手工',
        '法式甜點',
        '節慶禮盒',
    ];

    $products = [
        [
            'id' => 1,
            'title' => '經典原味生乳捲',
            'category' => '經典手工',
            'price' => 880,
            'badge' => '人氣熱銷',
            'description' => '使用北海道乳源與法國奶油，打造柔軟細膩口感。',
            'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=1200',
            'slug' => 'classic-milk-roll',
        ],
        [
            'id' => 2,
            'title' => '濃厚黑巧塔',
            'category' => '法式甜點',
            'price' => 1080,
            'badge' => '新品上市',
            'description' => '70% 比利時黑巧克力與榛果脆片層次融合。',
            'image' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?q=80&w=1200',
            'slug' => 'dark-chocolate-tart',
        ],
        [
            'id' => 3,
            'title' => '靜岡抹茶千層',
            'category' => '節慶禮盒',
            'price' => 1280,
            'badge' => '限定商品',
            'description' => '採用日本靜岡抹茶，茶韻尾勁濃厚。',
            'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=1200',
            'slug' => 'matcha-mille-crepe',
        ],
        [
            'id' => 3,
            'title' => '靜岡抹茶千層',
            'category' => '節慶禮盒',
            'price' => 1280,
            'badge' => '限定商品',
            'description' => '採用日本靜岡抹茶，茶韻尾勁濃厚。',
            'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=1200',
            'slug' => 'matcha-mille-crepe',
        ],
        [
            'id' => 2,
            'title' => '濃厚黑巧塔',
            'category' => '法式甜點',
            'price' => 1080,
            'badge' => '新品上市',
            'description' => '70% 比利時黑巧克力與榛果脆片層次融合。',
            'image' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?q=80&w=1200',
            'slug' => 'dark-chocolate-tart',
        ],
    ];
@endphp

@section('content')
    {{--
      全寬滿版面 Banner 區塊
      圖片尺寸建議：1920 x 600 px ~ 1920 x 800 px 之間
      重要規範：甜點或情境圖的視覺主體建議居中，寬度必須達 1920px 確保 4K 與大螢幕不模糊，上傳前必須壓縮至 400KB 以下維護載入效能
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

    {{-- 工具欄：篩選與搜尋 --}}
    <section class="p-product-toolbar">
        <div class="p-product-shell">
            <div class="p-product-toolbar__inner">
                <nav class="p-product-filter js-filter-group">
                    {{-- 透過 data 屬性與 JS 聯動，不寫死邏輯在 HTML --}}
                    @foreach (['全部商品', '經典手工', '法式甜點', '節慶禮盒'] as $category)
                        <button type="button"
                                class="p-product-filter__button {{ $loop->first ? 'is-active' : '' }}"
                                data-category="{{ $category }}">
                            {{ $category }}
                        </button>
                    @endforeach
                </nav>

                <div class="p-product-search">
                    <div class="p-product-search__field">
                        <input type="text" class="p-product-search__input" placeholder="Search..." data-product-search>
                        <i class="p-product-search__icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 產品列表區：參考美裕的簡約格狀 --}}
    <section class="p-product-list">
        <div class="p-product-shell">
            <div class="p-product-grid" data-product-grid>
                @foreach ($products as $product)
                    <article class="p-product-item js-fade-up"
                             data-category="{{ $product['category'] }}"
                             data-title="{{ strtolower($product['title']) }}">
                        <a href="{{ route('product.show', $product['slug']) }}" class="p-product-item__link">

                            {{--
                              產品網格圖片容器
                              圖片尺寸建議：800 x 800 px (1:1 正方形)
                              重要規範：型錄與電商網格最忌諱圖片長寬比不一。強制要求上架固定為 1:1 正方形，
                              若去背圖周圍需留白至少 10% 避免畫面太擁擠；圖檔建議控制在 150KB 內以利大量瀏覽
                            --}}
                            <div class="p-product-item__img-box">
                                @if($product['badge'])
                                    <span class="p-product-item__badge">{{ $product['badge'] }}</span>
                                @endif
                                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="p-product-item__img" loading="lazy">
                                <div class="p-product-item__overlay">
                                    <span class="view-text">DISCOVER MORE</span>
                                </div>
                            </div>
                            <div class="p-product-item__info">
                                <span class="p-product-item__cat">{{ $product['category'] }}</span>
                                <h2 class="p-product-item__title">{{ $product['title'] }}</h2>
                                <p class="p-product-item__price">NT$ {{ number_format($product['price']) }}</p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
    <script src="{{ asset('js/frontend/product.js') }}"></script>
@endpush
