@extends('frontend.layouts.app')

@section('title', '精品甜點｜產品介紹')

@push('styles')
    {{-- 一律採用外連樣式表，不撰寫任何行內 style 標籤 --}}
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@php
    $categories = ['全部商品', '經典手工', '法式甜點', '節慶禮盒'];

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
    <main class="p-product">
        {{--
            全寬滿版網頁 Banner 區塊
            圖片尺寸建議：1920 x 600 px ~ 1920 x 800 px 之間
            重要規範：甜點或情境圖的視覺主體建議居中，寬度必須達 1920px 確保 4K 與大螢幕不模糊，上傳前必須壓縮至 400KB 以下維護載入效能
        --}}
        <section class="page-banner">
            <div class="banner-img-wrap">
                <img src="https://images.unsplash.com/photo-1481391243133-f96216dcb5d2?q=80&w=1920&auto=format"
                    alt="Products Banner" class="banner-img js-parallax-img" loading="eager">
            </div>
            <div class="c-banner-block">
                <span class="c-banner-subtitle">Our Collections</span>
                <h1 class="c-banner-title">甜點藝廊</h1>
            </div>
        </section>

        {{-- 內容外殼限制器，確保全站寬度一致不爆版 --}}
        <div class="p-product-shell">
            {{-- 麵包屑導覽組件 --}}
            @include('components.frontend.breadcrumb')

            {{-- 產品列表核心區塊 --}}
            <section class="section-product-list">

                {{-- 分類導覽選單（全站共用下拉選單樣式組件） --}}
                <nav class="aside-nav slide js-fade-up" data-delay="0.2">
                    {{-- 行動裝置專用下拉選單按鈕 --}}
                    <div class="aside-nav__btn" id="js-aside-nav-btn">
                        <span class="word" id="js-aside-nav-current-text">選擇分類</span>
                        <span class="icon"></span>
                    </div>

                    {{-- 分類連結清單，透過單一迴圈渲染 --}}
                    <ul class="aside-nav__tab flex reset" id="js-aside-nav-list">
                        {{-- 預設全部商品 --}}
                        <li class="{{ !request('category') ? 'current' : '' }}">
                            <a href="{{ route('product.index') }}" title="全部商品">
                                <span>全部商品</span>
                            </a>
                        </li>

                        {{-- 後端資料庫分類迴圈渲染 --}}
                        @forelse ($catList ?? [] as $cat)
                            @php
                                $catName = $cat->descs->first()->name ?? '未命名';
                                $isCurrent = (request('category') == $cat->cat_id) ? 'current' : '';
                            @endphp
                            <li class="{{ $isCurrent }}">
                                <a href="{{ route('product.category', $cat->cat_id) }}" title="{{ $catName }}">
                                    <span>{{ $catName }}</span>
                                </a>
                            </li>
                        @empty
                            {{-- 分類無資料時的防呆安全空白，不干擾畫面運作 --}}
                        @endforelse
                    </ul>
                </nav>

                {{--
                    產品網格列表區塊
                    採用比照最新消息的 Grid 網格佈局，並融入甜點型錄獨有的動態特效
                --}}
                <div class="p-product-grid">
                    @forelse ($products as $product)
                        <article class="p-product-item js-fade-up" data-delay="0.2">
                            {{-- 路由跳轉至商品詳細內頁，Laravel 新手最易上手的陣列或物件讀取格式 --}}
                            <a href="{{ route('product.show', $product['slug'] ?? $product['id']) }}" class="p-product-item__link">

                                {{-- 產品圖片外殼區塊（透過 CSS 強制 1:1 正方形防呆） --}}
                                <div class="p-product-item__img-box">
                                    {{-- 若後台有設定促銷或分類標籤，則優雅呈現 --}}
                                    @if (!empty($product['badge']))
                                        <span class="p-product-item__badge">{{ $product['badge'] }}</span>
                                    @endif

                                    {{-- 產品主圖，搭載延遲載入（lazy loading）優化全站載入速度效能 --}}
                                    <img src="{{ $product['image'] ?? asset('images/default-product.jpg') }}"
                                         alt="{{ $product['title'] }}"
                                         class="p-product-item__img"
                                         loading="lazy">

                                    {{-- 滑過時淡入呈現的精品感遮罩層 --}}
                                    <div class="p-product-item__overlay">
                                        <span class="view-text">DISCOVER MORE</span>
                                    </div>
                                </div>

                                {{-- 產品文字描述與售價區塊 --}}
                                <div class="p-product-item__info">
                                    <span class="p-product-item__cat">{{ $product['category'] ?? '精選商品' }}</span>
                                    <h2 class="p-product-item__title">{{ $product['title'] ?? '未命名商品' }}</h2>
                                    <p class="p-product-item__price">NT$ {{ number_format($product['price'] ?? 0) }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        {{-- 防呆機制：當該分類商品被下架或完全沒有資料時的替代顯示（不破版） --}}
                        <div class="no-data-wrapper">
                            <p>目前此分類尚無商品上架，敬請期待。</p>
                        </div>
                    @endforelse
                </div>

                {{--
                    產品分頁器區塊
                    只有當資料量大於單頁顯示上限、具備多頁數時才會渲染渲染，避免畫面出現空白圓圈
                --}}
                @if ($items->hasPages())
                    <nav class="pagination-wrap js-fade-up">
                        @if (!$products->onFirstPage())
                            <a href="{{ $products->previousPageUrl() }}" class="page-btn" rel="prev">&larr;</a>
                        @endif

                        @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" class="page-btn {{ $page == $products->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach

                        @if ($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="page-btn" rel="next">&rarr;</a>
                        @endif
                    </nav>
                @endif

            </section>
        </div>
    </main>
@endsection

@push('scripts')
    {{-- 載入獨立的外部商品核心 JS 控制檔案，不寫任何 inline 魔法 --}}
    <script src="{{ asset('js/frontend/product.js') }}"></script>
@endpush
