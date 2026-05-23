@extends('frontend.layouts.app')

@section('title', '精品甜點｜產品介紹')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
@endpush

@section('content')
    <main class="p-product">
        {{--
          頁面全寬滿版橫幅標題區
          [建議圖片尺寸] 寬度 1920px，高度在 450px ~ 550px 之間，格式建議為 WebP 以優化載入效能。
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

        {{-- 內容寬度限制外殼 --}}
        <div class="p-product-shell">
            {{-- 麵包屑導覽組件 --}}
            @include('components.frontend.breadcrumb')

            {{-- 產品列表核心區塊 --}}
            <section class="section-product-list">

                {{-- 行動裝置響應式分類導覽選單 --}}
                <nav class="aside-nav slide js-fade-up" data-delay="0.2">
                    <div class="aside-nav__btn" id="js-aside-nav-btn">
                        <span class="word" id="js-aside-nav-current-text">選擇分類</span>
                        <span class="icon"></span>
                    </div>

                    {{-- 分類連結桌面端清單 --}}
                    <ul class="aside-nav__tab flex reset" id="js-aside-nav-list">
                        <li class="{{ !request('category') ? 'current' : '' }}">
                            <a href="{{ route('product.index') }}" title="全部商品">
                                <span>全部商品</span>
                            </a>
                        </li>

                        @forelse ($catList ?? [] as $cat)
                            <li class="{{ (request('category') == $cat->cat_id) ? 'current' : '' }}">
                                <a href="{{ route('product.category', $cat->cat_id) }}" title="{{ $cat->descs->first()->name ?? '未命名' }}">
                                    <span>{{ $cat->descs->first()->name ?? '未命名' }}</span>
                                </a>
                            </li>
                        @empty
                            {{-- 無分類時自動不渲染任何節點，確保 DOM 乾淨 --}}
                        @endforelse
                    </ul>
                </nav>

                {{-- 產品網格列表區塊（套用溫暖職人風格手繪鉛筆外框） --}}
                <div class="p-product-grid">
                    @forelse ($products ?? [] as $product)
                        <article class="p-product-item js-fade-up" data-delay="0.2">
                            <a href="{{ route('product.show', $product->product_id) }}" class="p-product-item__link">

                                {{--
                                  產品圖片容器（強制維持 1:1 正方形，上方覆蓋不規則手繪線條）
                                  [建議圖片尺寸] 800px x 800px 正方形商品圖，格式建議為 WebP。
                                --}}
                                <div class="p-product-item__img-box">
                                    {{-- 商品狀態標籤 --}}
                                    <span class="p-product-item__badge">熱銷經典</span>

                                    {{-- 商品圖片本體（內建資料缺失防呆機制，若無圖片則自動載入系統預設圖） --}}
                                    <img src="{{ $product->image_url ? asset('storage/' . $product->image_url) : asset('images/default/defult-500X500.png') }}"
                                         alt="{{ $product->descs->first()->title ?? 'COOOOKIE 餅乾' }}"
                                         class="p-product-item__img"
                                         loading="lazy">
                                </div>

                                {{-- 產品文字描述與售價區塊 --}}
                                <div class="p-product-item__info">
                                    <h2 class="p-product-item__title">{{ $product->descs->first()->title ?? '未命名商品' }}</h2>
                                    <p class="p-product-item__desc">{{ $product->descs->first()->description ?? '' }}</p>
                                    <p class="p-product-item__price">NT$ {{ number_format($product->price ?? 0) }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        {{-- 查無任何資料時的防呆空白狀態提示 --}}
                        <div class="no-data-wrapper">
                            <p>目前此分類尚無商品上架，敬請期待。</p>
                        </div>
                    @endforelse
                </div>

                {{-- 產品分頁器組件（自動安全偵測分頁方法是否存在，防止系統爆錯） --}}
                @if (isset($products) && method_exists($products, 'hasPages') && $products->hasPages())
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
    <script src="{{ asset('js/frontend/product.js') }}"></script>
@endpush
