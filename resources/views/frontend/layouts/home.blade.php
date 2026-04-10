@extends('frontend.layouts.app')


@push('styles')
    {{-- 改成專業的 Vite 引入方式 --}}
    {{-- @vite(['resources/css/home.scss']) --}}

    <link rel="stylesheet" href="{{ asset('css/home.css?v=1') }}">
@endpush



@php
    /**
     * 模擬後台傳入的最新消息資料
     * 用途：在 Controller 尚未串接前，確保前端版面開發正常
     * 格式：使用 stdClass 模擬資料庫 Eloquent Model 物件
     */

    $categories = [
        [
            'title' => '經典原味',
            'subtitle' => 'Classic Series',
            'img' => 'https://images.unsplash.com/photo-1590080876102-9473629d7e23?q=80&w=600&auto=format&fit=crop',
        ],
        [
            'title' => '濃厚黑巧',
            'subtitle' => 'Chocolate Series',
            'img' => 'https://images.unsplash.com/photo-1618923850107-d1a234d7a73a?q=80&w=600&auto=format&fit=crop',
        ],
        [
            'title' => '靜岡抹茶',
            'subtitle' => 'Tea Series',
            'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=600&auto=format&fit=crop',
        ],
    ];
@endphp

@section('content')
{{-- 1. Hero Section: 輪播 + 視差文字 --}}
<section class="hero-slider">
    <div class="swiper js-hero-swiper">
        <div class="swiper-wrapper">
            @forelse ($banners as $banner)
                <div class="swiper-slide">
                    {{-- 增加 title 屬性加強 SEO 與可存取性 --}}
                    <a href="{{ $banner->adv_link_url ?? '#' }}" class="d-block w-100 h-100 position-relative"
                        title="{{ $banner->adv_name ?? '查看更多詳細內容' }}">

                        {{-- 電腦版 banner 圖片 --}}
                        <img src="{{ asset('storage/' . $banner->adv_img_url) }}"
                            alt="{{ $banner->adv_name ?? 'banner' }}" class="banner-bg-img d-none d-md-block" />

                        {{-- 手機版 banner 圖片 (防呆：若無手機版圖則使用電腦版) --}}
                        <img src="{{ asset('storage/' . ($banner->adv_img_m_url ?? $banner->adv_img_url)) }}"
                            alt="{{ $banner->adv_name ?? 'banner' }}" class="banner-bg-img d-block d-md-none" />
                    </a>

                    <div class="banner-content-overlay">
                        <div class="banner-text-container">
                            <h2 class="banner-main-title">{{ $banner->desc->adv_name ?? '' }}</h2>
                            <p class="banner-sub-title">{{ $banner->desc->adv_subname ?? '' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="swiper-slide">
                    <div class="banner-bg-img banner-empty-fallback"></div>
                    <div class="banner-content-overlay">
                        <div class="banner-text-container">
                            <h2 class="banner-main-title">HANDMADE</h2>
                            <p class="banner-sub-title">職人手作，溫暖人心</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- 分頁器主體 --}}
        <div class="swiper-pagination"></div>
    </div>
</section>
    {{-- 2. Marquee: 動態跑馬燈 --}}
    <div class="marquee">
        <div class="marquee__inner">
            <span class="marquee__item">PREMIUM INGREDIENTS</span>
            <span class="marquee__item">HANDMADE WITH LOVE</span>
            <span class="marquee__item">NO ARTIFICIAL FLAVORS</span>
            <span class="marquee__item">FRESHLY BAKED</span>
            <span class="marquee__item">PREMIUM INGREDIENTS</span>
            <span class="marquee__item">HANDMADE WITH LOVE</span>
        </div>
    </div>

    {{-- 3. Intro Section: 品牌理念 --}}
    <section class="section-intro">
        <div class="intro-container">
            <div class="intro-grid">

                {{-- 左側：橫向長方形圖片 (3:2) - 768px 以下隱藏 --}}
                <div class="intro-img-wrap js-reveal-img">
                    <img src="https://images.unsplash.com/photo-1607114910421-a7c2b982d497?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        alt="精選天然食材製作工藝" class="intro-img js-parallax-img" loading="lazy">
                </div>

                {{-- 右側：品牌論述區 --}}
                <article class="intro-text">
                    <header>
                        <span class="section-tag js-fade-up">Brand Essence</span>
                        <h2 id="intro-title" class="section-title js-fade-up">
                            純粹，<br>所以不簡單
                        </h2>
                    </header>

                    <div class="deco-line js-line-grow"></div>

                    <p class="section-desc js-fade-up">
                        我們相信，美味的餅乾不需要複雜的添加物。嚴選法國發酵奶油、日本鑽石麵粉，以及台灣在地的新鮮土雞蛋。
                        每一個步驟都堅持手工製作，從麵團的攪拌到烘烤的火候，我們近乎偏執地追求完美，只為了帶給您最純粹的幸福滋味。
                    </p>

                    <footer>
                        <a href="{{ url('/about') }}" class="btn-read-more js-fade-up">
                            VIEW MORE
                        </a>
                    </footer>
                </article>

            </div>
        </div>
    </section>
{{-- 4. Process Section: 製作工藝 --}}
<section class="section-process">
    {{-- 裝飾圖案 --}}
    <div class="deco-shape deco-shape--process-1 js-rotate-anim"></div>
    <div class="deco-shape deco-shape--process-2 js-float-anim"></div>

    <div class="container">
        {{-- 區塊標頭：定義標籤與主標題 --}}
        <div class="section-header">
            <span class="section-tag js-fade-up">HOW WE MAKE IT</span>
            <h2 class="section-title js-fade-up">職人手作工藝</h2>
        </div>

        {{-- 流程步驟容器 --}}
        <div class="process-steps">
            {{-- 底層灰色進度線 --}}
            <div class="process-line"></div>
            {{-- 動態增長的彩色進度線 (由 JS 控制寬度) --}}
            <div class="process-line-fill js-line-fill"></div>

            {{-- 步驟 1 --}}
            <div class="process-step js-step-anim">
                <div class="step-icon-box">1</div>
                <h4 class="step-title">嚴選與過篩</h4>
                <p class="step-desc">手工過篩麵粉，<br>確保空氣感。</p>
            </div>

            <div class="process-step js-step-anim is-delay-200">
                <div class="step-icon-box">2</div>
                <h4 class="step-title">低溫發酵</h4>
                <p class="step-desc">36小時熟成，<br>喚醒食材風味。</p>
            </div>

            <div class="process-step js-step-anim is-delay-400">
                <div class="step-icon-box">3</div>
                <h4 class="step-title">精心烘烤</h4>
                <p class="step-desc">精準控溫，<br>鎖住酥脆口感。</p>
            </div>

            <div class="process-step js-step-anim is-delay-600">
                <div class="step-icon-box">4</div>
                <h4 class="step-title">完美包裝</h4>
                <p class="step-desc">真空密封，<br>傳遞新鮮心意。</p>
            </div>
        </div>
    </div>
</section>
{{-- 產品系列 --}}
@if (isset($categories) && count($categories) > 0)
    <section class="section-product-cat">
        <div class="container-1280">
            <header class="section-header js-fade-up">
                <span class="section-tag">EXPLORE COLLECTIONS</span>
                <h2 id="cat-heading" class="section-title">產品系列</h2>
            </header>

            <div class="swiper-wrap js-fade-up">
                <div class="swiper js-product-cat-swiper">
                    <div class="swiper-wrapper">
                        @forelse ($categories as $cat)
                            <div class="swiper-slide">
                                <a href="{{ url('/products') }}" class="product-cat-card" title="查看 {{ $cat['title'] }}">
                                    <article class="product-cat-img-box">
                                        <img src="{{ $cat['img'] }}" alt="{{ $cat['title'] }}" class="product-cat-img" loading="lazy" onload="this.classList.add('is-loaded')">
                                        <div class="product-cat-overlay">
                                            <div class="product-cat-glass-box">
                                                <h3 class="product-cat-title">{{ $cat['title'] }}</h3>
                                                <p class="product-cat-subtitle">{{ $cat['subtitle'] }}</p>
                                                <span class="product-cat-more">VIEW MORE</span>
                                            </div>
                                        </div>
                                    </article>
                                </a>
                            </div>
                        @empty
                            <p>尚無產品分類數據</p>
                        @endforelse
                    </div>
                </div>

                {{-- 控制元件 --}}
                <div class="control-box">
                    <button type="button" class="swiper-button swiper-prev js-product-cat-prev"></button>
                    <div class="swiper-dots js-product-cat-pagination"></div>
                    <button type="button" class="swiper-button swiper-next js-product-cat-next"></button>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- 最新消息 --}}
@if (isset($homeNews) && $homeNews->isNotEmpty())
    <section class="section-news">
        <div class="container-1280">
            <header class="section-header js-fade-up">
                <span class="section-tag">Latest News</span>
                <h2 id="news-heading" class="section-title">最新消息</h2>
            </header>

            <div class="swiper-wrap js-fade-up">
                <div class="swiper js-news-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($homeNews as $news)
                            <div class="swiper-slide">
                                <article class="h-100">
                                    <a href="{{ route('news.show', $news->news_id) }}" class="news-card">
                                        <div class="news-img-box">
                                            <img src="{{ $news->image_url ? asset('storage/' . $news->image_url) : asset('images/default-news.jpg') }}"
                                                alt="{{ $news->desc->title ?? 'News' }}" class="news-img" loading="lazy" onload="this.classList.add('is-loaded')">
                                            <div class="news-date-badge">
                                                <time datetime="{{ $news->created_at->format('Y-m-d') }}">
                                                    <span class="day">{{ $news->created_at->format('d') }}</span>
                                                    <span class="month">{{ $news->created_at->format('n') }}月</span>
                                                </time>
                                            </div>
                                        </div>
                                        <div class="news-content">
                                            <span class="news-category">{{ $news->category->desc->name ?? 'News' }}</span>
                                            <h3 class="news-title">{{ $news->desc->title ?? '' }}</h3>
                                            <div class="news-footer">
                                                <span class="news-btn-text">READ MORE</span>
                                                <span class="news-arrow-icon"></span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="control-box">
                    <button type="button" class="swiper-button swiper-prev js-news-prev"></button>
                    <div class="swiper-dots js-news-pagination"></div>
                    <button type="button" class="swiper-button swiper-next js-news-next"></button>
                </div>
            </div>
        </div>
    </section>
@endif

    {{-- 7. Tasting Section: 試吃申請 --}}
    <section class="i-tasting-section">
        @include('components.frontend.i-tasting')
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/frontend/home.js') }}"></script>
    <script>
        $(function() {

            var productCatSwiper = new Swiper('.js-product-cat-swiper', {
                loop: true,
                spaceBetween: 10,
                slidesPerView: 1,
                navigation: {
                    nextEl: ".section-product-cat .js-product-cat-next",
                    prevEl: ".section-product-cat .js-product-cat-next"
                },
                pagination: {
                    el: '.js-product-cat-pagination',
                    clickable: true,
                    type: 'fraction',
                    formatFractionCurrent: function(number) {
                        return ('0' + number).slice(-2);
                    },
                    formatFractionTotal: function(number) {
                        return ('0' + number).slice(-2);
                    },
                    renderFraction: function (currentClass, totalClass, index) {
                return `<span class="${currentClass}"></span>` +
                            `<span class="gap"></span>` +
                            `<span class="${totalClass}"></span>`;
            }
                },
                breakpoints: {
                    744: {
                        spaceBetween: 20,
                        slidesPerView: 2
                    },
                    769: {
                        spaceBetween: 20,
                        slidesPerView: 3
                    },
                    1440: {
                        spaceBetween: 49,
                        slidesPerView: 3
                    }
                }
            });

            var newsSwiper = new Swiper('.js-news-swiper', {
                loop: true,
                spaceBetween: 10,
                slidesPerView: 1,
                navigation: {
                    nextEl: ".section-news .js-news-next",
                    prevEl: ".section-news .js-news-next"
                },
                pagination: {
                    el: '.js-news-pagination',
                    clickable: true,
                    type: 'fraction',
                    formatFractionCurrent: function(number) {
                        return ('0' + number).slice(-2);
                    },
                    formatFractionTotal: function(number) {
                        return ('0' + number).slice(-2);
                    },
                    renderFraction: function (currentClass, totalClass, index) {
                return `<span class="${currentClass}"></span>` +
                            `<span class="gap"></span>` +
                            `<span class="${totalClass}"></span>`;
            }
                },
                breakpoints: {
                    744: {
                        spaceBetween: 20,
                        slidesPerView: 2
                    },
                    769: {
                        spaceBetween: 20,
                        slidesPerView: 3
                    },
                    1440: {
                        spaceBetween: 49,
                        slidesPerView: 3
                    }
                }
            });

        });
    </script>
@endpush
