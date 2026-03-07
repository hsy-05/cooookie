{{-- about.blade.php --}}
@extends('frontend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    <main class="smooth-wrapper">
        {{-- 全域雜訊優化 --}}
        <div class="global-noise"></div>

        {{-- 1. PAGE BANNER --}}
        <section class="page-banner">
            <div class="page-banner__bg">
                {{-- Banner 圖片尺寸需求：1920x1080px --}}
                <img src="https://images.unsplash.com/photo-1572347570868-af6e3c3d6be7?q=80&w=1920" alt="COOOOKIE 手作工藝"
                    class="js-parallax-img">
                <div class="page-banner__overlay"></div>
            </div>

            <div class="container page-banner__container">
                <div class="page-banner__content js-hero-text">
                    <nav class="page-banner__meta">EST. 2024 • TAINAN</nav>
                    <h2 class="page-banner__title">
                        THE ART OF <br class="d-md-none">
                        <span class="text-italic">IMPERFECT</span> PERFECTION.
                    </h2>
                    <p class="page-banner__desc">
                        不完美的圓，才是手作的靈魂。<br>
                        我們拒絕工業標準化，用溫度定義甜點。
                    </p>
                </div>

                <div class="page-banner__deco js-rotate-scroll">
                    <svg viewBox="0 0 100 100" width="100%" height="100%">
                        <path id="curve" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0"
                            fill="transparent" />
                        <text>
                            <textPath xlink:href="#curve" fill="#FFFFFF">HANDCRAFTED • PREMIUM • COOOOKIE •</textPath>
                        </text>
                    </svg>
                </div>
            </div>
        </section>

        {{-- Marquee --}}
        <div class="marquee">
            <div class="marquee__inner">
                <span class="marquee__item">PREMIUM INGREDIENTS</span>
                <span class="marquee__item">HANDMADE WITH LOVE</span>
                <span class="marquee__item">NO ARTIFICIAL FLAVORS</span>
                <span class="marquee__item">FRESHLY BAKED</span>
                <span class="marquee__item">UNAPOLOGETICALLY HANDMADE • </span>
            </div>
        </div>

        <div class="container-1600">
            {{-- 麵包屑 --}}
            @include('components.frontend.breadcrumb')

            {{-- 品牌故事 --}}
            <section id="story" class="section story">
                <div class="container">
                    <div class="story__grid">
                        <div class="story__visual">
                            <div class="story-mask js-story-reveal">
                                {{-- 圖片需求：800x1000px --}}
                                <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=800"
                                    alt="烘焙職人" class="story-img-anim" loading="lazy">
                            </div>
                        </div>
                        <div class="story__content js-fade-up">
                            <div class="section-header text-left">
                                <span class="section-subtitle">OUR ORIGIN</span>
                                <h2 class="section-title">一間「不聽勸」的<br>甜點工作室</h2>
                                <div class="section-line"></div>
                            </div>
                            <article class="story__text">
                                <p>創辦人思涵曾在法國藍帶學院學習，回台後卻成了同業眼中的「異類」。別人說「餅乾要圓才好裝罐」，她偏要保留手揉麵團那種張狂的不規則邊緣。</p>
                                <p>2024年，在台南巷弄的老宅裡，COOOOKIE 誕生了。這裡沒有精密儀器，只有對溫度的偏執，和對食材的絕對誠實。</p>
                            </article>
                            <blockquote class="story__quote">
                                「甜點不該是冰冷的複製品，<br>而是一份有溫度的生活提案。」
                            </blockquote>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- 核心價值區塊 (釘選 + 上下預覽輪播) --}}
        <section class="values-section js-values-pin">
            <div class="values-sticky-container">
                {{-- A. 標題區域 --}}
                <div class="values-top-bar">
                    <div class="values-header js-fade-up">
                        <span class="section-subtitle">OUR PHILOSOPHY</span>
                        <h2 class="section-title">堅持，是不妥協的溫柔</h2>
                        <div class="values-deco-line"></div>
                    </div>
                </div>

                {{-- 內容區域 --}}
                <div class="container values-content-body">
                    {{-- 左側：文字輪播 (結構優化以支援 prev/next 樣式) --}}
                    <div class="values-text-col">
                        <div class="v-text-viewport">
                            <div class="v-item js-v-item" data-index="0">
                                <div class="v-meta">
                                    <span class="v-num">01</span>
                                    <div class="v-progress">
                                        <div class="v-bar"></div>
                                    </div>
                                </div>
                                <h3 class="v-head">Purity 純淨</h3>
                                <p class="v-desc">成分表乾淨得像一張白紙，我們只選用最純粹的發酵奶油與鑽石麵粉，拒絕任何化學捷徑。</p>
                            </div>
                            <div class="v-item js-v-item" data-index="1">
                                <div class="v-meta">
                                    <span class="v-num">02</span>
                                    <div class="v-progress">
                                        <div class="v-bar"></div>
                                    </div>
                                </div>
                                <h3 class="v-head">Craft 匠心</h3>
                                <p class="v-desc">機器無法複製手揉麵團的呼吸感，每一塊餅乾都承載著職人的指尖溫度與對完美的偏執。</p>
                            </div>
                            <div class="v-item js-v-item" data-index="2">
                                <div class="v-meta">
                                    <span class="v-num">03</span>
                                    <div class="v-progress">
                                        <div class="v-bar"></div>
                                    </div>
                                </div>
                                <h3 class="v-head">Eco 永續</h3>
                                <p class="v-desc">美味不該是地球的負擔，堅持減塑包裝與在地食材，守護我們共同的家。</p>
                            </div>
                        </div>
                    </div>

                    {{-- 右側：圖片輪播 --}}
                    <div class="values-img-col">
                        <div class="v-img-frame">
                            {{-- 圖片需求：800x600px (4:3) --}}
                            <img src="https://images.unsplash.com/photo-1669889498104-da725d9e09a9?q=80&w=800"
                                class="v-pic active" alt="Purity">
                            <img src="https://images.unsplash.com/photo-1427025635812-0a30f21071e4?q=80&w=800"
                                class="v-pic" alt="Craft">
                            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=800" class="v-pic"
                                alt="Eco">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Parallax Banner --}}
        <section class="parallax-banner js-diag-parallax"
            style="background-image: url('https://images.unsplash.com/photo-1587678777691-3d0df519c046?q=80&w=1920');">
            <div class="parallax-banner__overlay"></div>
            <div class="parallax-banner__content js-fade-up">
                <h2 class="parallax-title">每一口酥脆，<br>都是時間的沈澱。</h2>
                <p class="parallax-subtitle">我們用 365 天的嘗試，換來這 3 分鐘的極致享受。</p>
            </div>
        </section>

        {{-- 靈魂食材 (圓形覆蓋設計) --}}
        <section class="section ingredients-sec bg-white">
            <div class="container">
                <div class="text-center mb-5 js-fade-up">
                    <span class="section-subtitle">FINEST INGREDIENTS</span>
                    <h2 class="section-title">靈魂食材</h2>
                </div>

                <div class="ing-grid">
                    {{-- Item 1 --}}
                    <div class="ing-item js-ing-anim">
                        <figure class="ing-circle">
                            {{-- 圖片需求：600x600px --}}
                            <img src="https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?q=80&w=600"
                                alt="Butter" loading="lazy">
                            <figcaption class="ing-overlay">
                                <div class="ing-text">
                                    <h4>法國 Isigny 奶油</h4>
                                    <div class="ing-line"></div>
                                    <span>NO.1 FERMENTED BUTTER</span>
                                </div>
                            </figcaption>
                        </figure>
                    </div>

                    {{-- Item 2 --}}
                    <div class="ing-item js-ing-anim">
                        <figure class="ing-circle">
                            <img src="https://images.unsplash.com/photo-1652283319196-9288add2d60c?q=80&w=600"
                                alt="Flour" loading="lazy">
                            <figcaption class="ing-overlay">
                                <div class="ing-text">
                                    <h4>日本鑽石麵粉</h4>
                                    <div class="ing-line"></div>
                                    <span>PREMIUM WHEAT FLOUR</span>
                                </div>
                            </figcaption>
                        </figure>
                    </div>

                    {{-- Item 3 --}}
                    <div class="ing-item js-ing-anim">
                        <figure class="ing-circle">
                            <img src="https://images.unsplash.com/photo-1586802990181-a5771596eaea?q=80&w=600"
                                alt="Eggs" loading="lazy">
                            <figcaption class="ing-overlay">
                                <div class="ing-text">
                                    <h4>台灣非籠飼雞蛋</h4>
                                    <div class="ing-line"></div>
                                    <span>CAGE-FREE EGGS</span>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </section>

        @include('components.frontend.i-tasting')
    </main>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    {{-- 這裡只需載入該頁專用的 JS --}}
    <script src="{{ asset('js/frontend/about.js') }}"></script>
@endpush
