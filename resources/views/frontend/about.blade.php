{{-- about.blade.php --}}
@extends('frontend.layouts.app')

@section('title', '關於我們 | COOOOKIE - 職人手作甜點')

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
                <img src="https://images.unsplash.com/photo-1572347570868-af6e3c3d6be7?q=80&w=1920"
                     alt="COOOOKIE 手作工藝"
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
                        <path id="curve" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" fill="transparent" />
                        <text><textPath xlink:href="#curve" fill="#FFFFFF">HANDCRAFTED • PREMIUM • COOOOKIE •</textPath></text>
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
            @include('components.frontend.breadcrumb')

            {{-- 2. 品牌故事 --}}
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

        {{-- 3. 核心價值區塊 (釘選 + 上下預覽輪播) --}}
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

                {{-- B. 內容區域 --}}
                <div class="container values-content-body">
                    {{-- 左側：文字輪播 (結構優化以支援 prev/next 樣式) --}}
                    <div class="values-text-col">
                        <div class="v-text-viewport">
                            <div class="v-item js-v-item" data-index="0">
                                <div class="v-meta">
                                    <span class="v-num">01</span>
                                    <div class="v-progress"><div class="v-bar"></div></div>
                                </div>
                                <h3 class="v-head">Purity 純淨</h3>
                                <p class="v-desc">成分表乾淨得像一張白紙，我們只選用最純粹的發酵奶油與鑽石麵粉，拒絕任何化學捷徑。</p>
                            </div>
                            <div class="v-item js-v-item" data-index="1">
                                <div class="v-meta">
                                    <span class="v-num">02</span>
                                    <div class="v-progress"><div class="v-bar"></div></div>
                                </div>
                                <h3 class="v-head">Craft 匠心</h3>
                                <p class="v-desc">機器無法複製手揉麵團的呼吸感，每一塊餅乾都承載著職人的指尖溫度與對完美的偏執。</p>
                            </div>
                            <div class="v-item js-v-item" data-index="2">
                                <div class="v-meta">
                                    <span class="v-num">03</span>
                                    <div class="v-progress"><div class="v-bar"></div></div>
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
                            <img src="https://images.unsplash.com/photo-1669889498104-da725d9e09a9?q=80&w=800" class="v-pic active" alt="Purity">
                            <img src="https://images.unsplash.com/photo-1427025635812-0a30f21071e4?q=80&w=800" class="v-pic" alt="Craft">
                            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=800" class="v-pic" alt="Eco">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. Parallax Banner --}}
        <section class="parallax-banner js-diag-parallax"
                 style="background-image: url('https://images.unsplash.com/photo-1587678777691-3d0df519c046?q=80&w=1920');">
            <div class="parallax-banner__overlay"></div>
            <div class="parallax-banner__content js-fade-up">
                <h2 class="parallax-title">每一口酥脆，<br>都是時間的沈澱。</h2>
                <p class="parallax-subtitle">我們用 365 天的嘗試，換來這 3 分鐘的極致享受。</p>
            </div>
        </section>

        {{-- 5. 靈魂食材 (圓形覆蓋設計) --}}
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
                            <img src="https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?q=80&w=600" alt="Butter" loading="lazy">
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
                            <img src="https://images.unsplash.com/photo-1652283319196-9288add2d60c?q=80&w=600" alt="Flour" loading="lazy">
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
                            <img src="https://images.unsplash.com/photo-1586802990181-a5771596eaea?q=80&w=600" alt="Eggs" loading="lazy">
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            gsap.registerPlugin(ScrollTrigger);
            window.addEventListener("resize", () => ScrollTrigger.refresh());

            // ==========================================
            // 參數設定區 (可在此快速調整速度與距離)
            // ==========================================
            const SCROLL_SPEED = 1000; // 數值越小，滑動切換越快 (原 3000)
            const HEADER_OFFSET = "30px"; // 避開 Header 的高度

            // 1. 通用 Fade Up (可重複觸發)
            gsap.utils.toArray('.js-fade-up').forEach(el => {
                gsap.fromTo(el, { autoAlpha: 0, y: 30 }, {
                    autoAlpha: 1, y: 0, duration: 1, ease: "power2.out",
                    scrollTrigger: { trigger: el, start: "top 90%", toggleActions: "play none none reverse" }
                });
            });

            // 2. Banner 文字與視差
            gsap.set(".js-hero-text", { autoAlpha: 0, y: 30 });
            gsap.to(".js-hero-text", { autoAlpha: 1, y: 0, duration: 1.5, ease: "power2.out", delay: 0.5 });
            gsap.to(".js-parallax-img", {
                yPercent: 20, ease: "none",
                scrollTrigger: { trigger: ".page-banner", start: "top top", end: "bottom top", scrub: true }
            });

            gsap.to(".js-rotate-scroll", {
                rotation: 360, ease: "none",
                scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 2 }
            });

            // 3. 核心價值：輪播動畫 (優化版：單一聚焦)
            const vItems = gsap.utils.toArray(".js-v-item");
            const vPics = gsap.utils.toArray(".v-pic");
            const totalItems = vItems.length;

            // 建立 TimeLine
            let vTl = gsap.timeline({
                scrollTrigger: {
                    trigger: ".js-values-pin",
                    start: `top ${HEADER_OFFSET}`, // 從 Header 下方開始 Pin
                    end: `+=${SCROLL_SPEED}`,      // 滾動距離 (控制速度)
                    pin: true,
                    scrub: 0.5,                    // 增加平滑感
                    invalidateOnRefresh: true
                }
            });

            // 迴圈建立動畫邏輯
            // 邏輯：Item N 進場 -> Item N 離場 -> Item N+1 進場
            vItems.forEach((item, i) => {
                const bar = item.querySelector('.v-bar');
                const title = item.querySelector('.v-head');

                // A. 進場 (In)
                // 設為絕對定位重疊，透過 opacity 和 y 控制切換
                if (i === 0) {
                    // 第1個項目預設顯示，不需要進場動畫，只需設定初始狀態
                    gsap.set(item, { opacity: 1, y: "0%" });
                    gsap.set(bar, { width: "100%" });
                    gsap.set(title, { color: "#8c6a4b" });
                } else {
                    // 其他項目：進場動畫
                    vTl.to(item, { opacity: 1, y: "0%", duration: 1 });
                    vTl.to(bar, { width: "100%", duration: 0.8 }, "<"); // 同步執行
                    vTl.to(title, { color: "#8c6a4b", duration: 0.8 }, "<");
                    // 圖片進場
                    vTl.to(vPics[i], { clipPath: "inset(0% 0% 0% 0%)", duration: 1 }, "<");
                }

                // B. 離場 (Out) - 除了最後一個項目
                if (i < totalItems - 1) {
                    // 加入一個標籤讓畫面停留一下，不要馬上切走
                    vTl.to({}, { duration: 0.5 });

                    vTl.to(item, { opacity: 0, y: "-20%", duration: 1 }); // 往上淡出
                    vTl.to(bar, { width: "0%", duration: 0.5 }, "<");
                    // 圖片不需要特別離場，因為會被下一張蓋過 (clip-path inset 0)
                }
            });


            // 4. 靈魂食材動畫
            // 遍歷所有具有 .js-ing-anim 類別的元素
            gsap.utils.toArray('.js-ing-anim').forEach((el) => {
                // 取得圖片、文字和 overlay 元素
                const img = el.querySelector('img');
                const text = el.querySelector('.ing-text');
                const overlay = el.querySelector('.ing-overlay');

                // 建立 GSAP 動畫時間軸並設置 ScrollTrigger 來觸發動畫
                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: el,               // 設定觸發元素為 el
                        start: "top 85%",           // 當元素滾動到視窗 85% 的位置時開始
                        toggleActions: "play none none reverse" // 滾動時控制動畫播放
                    }
                });

                // 元素的動畫過程
                tl.fromTo(el, { scale: 0.9, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.8, ease: "back.out(1.2)" }) // 開始時縮小、透明度為 0
                .fromTo(img, { scale: 1.2 }, { scale: 1, duration: 1.2, ease: "power2.out" }, "<") // 圖片從放大 1.2 到正常大小
                .fromTo(text, { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8 }, "-=0.6") // 文字從下方進場，並漸顯
                .add(() => {
                    overlay.classList.add('is-visible'); // 動畫完成後，顯示 overlay
                });
            });


            // 5. Parallax Banner 對角線
            gsap.fromTo(".js-diag-parallax",
                { backgroundPosition: "0% 0%" },
                {
                    backgroundPosition: "100% 100%", ease: "none",
                    scrollTrigger: {
                        trigger: ".js-diag-parallax", start: "top bottom", end: "bottom top", scrub: true
                    }
                }
            );
        });
    </script>
@endpush
