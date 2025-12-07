{{-- about.blade.php (2025 專業重構版) --}}
@extends('frontend.layouts.app')

@section('title', '關於我們 | COOOOKIE - 叛逆的甜點哲學')

{{-- SEO Meta Data --}}
@section('meta')
    <meta name="description" content="COOOOKIE 是一間不聽勸的甜點工作室。我們拒絕工業標準化，堅持手作的不完美與溫度。使用日本鑽石麵粉、法國發酵奶油，為您獻上最真實的美味。">
    <meta property="og:title" content="關於我們 | COOOOKIE - 叛逆的甜點哲學">
    <meta property="og:image" content="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=1200&auto=format&fit=crop">
    <meta property="og:type" content="website">
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    {{-- 預加載字體資源 --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;500;700;900&family=Inter:wght@300;500;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<main class="smooth-wrapper">
    {{-- 全域背景紋理（提升質感的關鍵，模擬紙張觸感） --}}
    <div class="global-noise"></div>

    {{-- 1. HERO 區塊：雜誌級排版 + 強烈視差 --}}
    <section class="hero">
        <div class="container hero__layout">
            {{-- 文字層 (前景) --}}
            <div class="hero__content">
                <div class="hero__meta js-fade-up">EST. 2024 • TAINAN</div>
                <h1 class="hero__title">
                    <span class="line-mask"><span class="js-text-reveal">THE ART OF</span></span>
                    <span class="line-mask"><span class="js-text-reveal text-italic">IMPERFECT</span></span>
                    <span class="line-mask"><span class="js-text-reveal">PERFECTION.</span></span>
                </h1>
                <p class="hero__desc js-fade-up">
                    不完美的圓，才是手作的靈魂。<br>
                    我們是 COOOOKIE，一群拒絕工業標準化的甜點叛逆者。
                </p>

                {{-- 磁吸按鈕 --}}
                <div class="js-fade-up">
                    <a href="#story" class="hero__btn js-magnetic js-scroll-to" aria-label="閱讀品牌故事">
                        <span class="btn-circle"></span>
                        <span class="btn-text">探索旅程</span>
                        <span class="btn-icon">↓</span>
                    </a>
                </div>
            </div>

            {{-- 圖片層 (有機形狀 + 視差) --}}
            <div class="hero__visual">
                <div class="hero__img-mask js-clip-reveal">
                    {{-- Hero 圖片：Eager Load + 寬高設定避免 CLS --}}
                    <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=1400&auto=format&fit=crop"
                         alt="COOOOKIE 手作工藝展示"
                         class="hero__img js-parallax-img"
                         loading="eager" width="800" height="1000">
                </div>
                {{-- 裝飾元素 --}}
                <div class="hero__deco js-rotate-scroll">
                    <svg viewBox="0 0 100 100" width="120" height="120">
                        <path id="curve" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" fill="transparent"/>
                        <text>
                            <textPath xlink:href="#curve" fill="#B08968">
                                HANDCRAFTED • PREMIUM • COOOOKIE •
                            </textPath>
                        </text>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Marquee: 動態跑馬燈 --}}
    <div class="marquee">
        <div class="marquee__inner">
            <span class="marquee__item">PREMIUM INGREDIENTS</span>
            <span class="marquee__item">HANDMADE WITH LOVE</span>
            <span class="marquee__item">NO ARTIFICIAL FLAVORS</span>
            <span class="marquee__item">FRESHLY BAKED</span>
            <span class="marquee__item">UNAPOLOGETICALLY HANDMADE • </span>
            <span class="marquee__item">NO PRESERVATIVES • PURE JOY • </span>
        </div>
    </div>

    {{-- 面包屑 (SEO結構) --}}
    <div class="breadcrumb">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol>
                    <li><a href="{{ url('/') }}">首頁</a></li>
                    <li aria-current="page">關於我們</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- 3. 品牌故事：Z字排版 + 互動懸浮標籤 --}}
    <section id="story" class="section story">
        <div class="container">
            <div class="story__grid">
                {{-- 左側視覺 --}}
                <div class="story__visual js-fade-right">
                    <div class="img-frame">
                        <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=1000&auto=format&fit=crop"
                             alt="烘焙職人專注的神情"
                             class="js-parallax-slow"
                             loading="lazy" width="800" height="1000">
                    </div>
                </div>

                {{-- 右側文案 --}}
                <div class="story__content js-fade-left">
                    <div class="section-header">
                        <span class="section-subtitle">OUR ORIGIN</span>
                        <h2 class="section-title">一間「不聽勸」的<br>甜點工作室</h2>
                        <div class="section-line js-line-grow"></div>
                    </div>

                    <article class="story__text">
                        <p>
                            創辦人思涵曾在法國藍帶學院學習，回台後卻成了同業眼中的「異類」。
                            別人說「餅乾要圓才好裝罐」，她偏要保留手揉麵團那種張狂的不規則邊緣；
                            別人追求「半年保鮮期」，她堅持「當日現烤，三天內最好吃」。
                        </p>
                        <p>
                            2024年，在台南巷弄的老宅裡，COOOOKIE 誕生了。
                            這裡沒有精密儀器，只有對溫度的偏執，和對食材的絕對誠實。
                        </p>
                    </article>
                    <blockquote class="story__quote js-fade-up">
                        「甜點不該是冰冷的複製品，<br>而是一份有溫度的生活提案。」
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. 視差全幅背景：沈浸式體驗 --}}
    <section class="parallax-banner js-parallax-bg" style="background-image: url('https://images.unsplash.com/photo-1587678777691-3d0df519c046?q=80&w=1920&auto=format&fit=crop');">
        <div class="parallax-banner__overlay"></div>
        <div class="container parallax-banner__content js-fade-up">
            <h2 class="parallax-title">每一口酥脆，<br>都是時間的沈澱。</h2>
            <p class="parallax-subtitle">我們用 365 天的嘗試，換來這 3 分鐘的極致享受。</p>
        </div>
    </section>

    {{-- 5. 核心價值：極簡設計 + 線條互動 --}}
    <section class="section values">
        <div class="container">
            <div class="section-header center js-fade-up">
                <span class="section-subtitle">OUR PHILOSOPHY</span>
                <h2 class="section-title">堅持，是不妥協的溫柔</h2>
            </div>

            <div class="values__grid">
                {{-- Value 1 --}}
                <div class="value-card js-stagger-up">
                    <div class="value-num">01</div>
                    <h3>純淨 Purity</h3>
                    <p>拒絕看不懂的化學名詞。法國發酵奶油、日本鑽石麵粉、台灣非籠飼雞蛋，成分表乾淨得像一張白紙。</p>
                    <div class="value-deco-line"></div>
                </div>
                {{-- Value 2 --}}
                <div class="value-card js-stagger-up">
                    <div class="value-num">02</div>
                    <h3>匠心 Craft</h3>
                    <p>機器可以複製形狀，但無法複製口感。我們堅持手工拌合，保留麵團的筋性與呼吸感，讓每一片餅乾都有個性。</p>
                    <div class="value-deco-line"></div>
                </div>
                {{-- Value 3 --}}
                <div class="value-card js-stagger-up">
                    <div class="value-num">03</div>
                    <h3>永續 Eco</h3>
                    <p>美味不該是地球的負擔。包裝減塑、優先選用在地食材以減少碳足跡，這是我們對環境的承諾。</p>
                    <div class="value-deco-line"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Ingredients Section: 嚴選食材 (新增區塊) --}}
    <section class="section-ingredients">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-tag js-fade-up">FINEST INGREDIENTS</span>
                <h2 class="section-title js-fade-up">三大靈魂食材</h2>
            </div>
            <div class="ing-grid">
                <div class="ing-card js-fade-up" data-delay="0.1">
                    <div class="ing-img-box">
                        <img src="https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?q=80&w=600&auto=format&fit=crop" alt="Butter">
                    </div>
                    <h3 class="ing-title">法國 Isigny 奶油</h3>
                    <p class="ing-desc">來自諾曼第地區，擁有獨特榛果香氣與絲滑口感，是餅乾酥脆的秘密。</p>
                </div>
                <div class="ing-card js-fade-up" data-delay="0.2">
                    <div class="ing-img-box">
                        <img src="https://images.unsplash.com/photo-1652283319196-9288add2d60c?q=80&w=600&auto=format&fit=crop" alt="Flour">
                    </div>
                    <h3 class="ing-title">日本鑽石麵粉</h3>
                    <p class="ing-desc">粉質細緻，保濕性佳，能呈現出小麥最原始的甜味與香氣。</p>
                </div>
                <div class="ing-card js-fade-up" data-delay="0.3">
                    <div class="ing-img-box">
                        <img src="https://images.unsplash.com/photo-1548169874-53e85f753f1e?q=80&w=600&auto=format&fit=crop" alt="Eggs">
                    </div>
                    <h3 class="ing-title">台灣紅殼土雞蛋</h3>
                    <p class="ing-desc">每日新鮮直送，蛋黃飽滿濃郁，為餅乾增添一抹金黃色澤與蛋香。</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 試吃申請區塊 --}}
    @include('components.frontend.i-tasting')
</main>

@endsection

@push('scripts')
{{-- GSAP Core & ScrollTrigger (使用 CDN 加速) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

    // ============================================
    // 1. 全域動畫配置 (UX核心調整)
    // start: "top 80%" -> 提早觸發，解決「滑到中間才顯示」的問題
    // toggleActions: "play reverse play reverse" -> 往上滑時倒帶，保持動態
    // ============================================
    const revealConfig = {
        toggleActions: "play reverse play reverse",
        start: "top 100%",
        markers: false
    };

    // 2. Hero 區塊：視差滾動
    // 圖片移動速度比文字慢，產生深度
    gsap.to(".js-parallax-img", {
        yPercent: 15,
        ease: "none",
        scrollTrigger: {
            trigger: ".hero",
            start: "top top",
            end: "bottom top",
            scrub: true
        }
    });

    // 3. Hero 文字逐行揭示 (Text Reveal)
    // 延遲極短，確保首屏一載入就看得到
    gsap.utils.toArray('.js-text-reveal').forEach((el, i) => {
        gsap.from(el, {
            yPercent: 100,
            opacity: 0,
            duration: 1.2,
            ease: "power4.out",
            delay: 0.1 + (i * 0.1)
        });
    });

    // 4. Hero 圖片遮罩揭示 (Clip Path)
    gsap.from(".js-clip-reveal", {
        clipPath: "inset(100% 0 0 0)",
        duration: 1.5,
        ease: "power3.inOut",
        delay: 0.2
    });

    // 5. 通用淡入上浮 (Fade Up)
    gsap.utils.toArray('.js-fade-up').forEach(el => {
        gsap.from(el, {
            scrollTrigger: { trigger: el, ...revealConfig },
            y: 40,
            opacity: 0,
            duration: 0.8,
            ease: "power3.out"
        });
    });

    // 6. 左右滑入
    gsap.utils.toArray('.js-fade-right').forEach(el => {
        gsap.from(el, {
            scrollTrigger: { trigger: el, ...revealConfig },
            x: -40, opacity: 0, duration: 1, ease: "power3.out"
        });
    });

    gsap.utils.toArray('.js-fade-left').forEach(el => {
        gsap.from(el, {
            scrollTrigger: { trigger: el, ...revealConfig },
            x: 40, opacity: 0, duration: 1, ease: "power3.out"
        });
    });

    // 7. 交錯動畫 (Stagger Up)
    gsap.utils.toArray('.js-stagger-up').forEach((el, index) => {
        gsap.from(el, {
            scrollTrigger: { trigger: el, ...revealConfig },
            y: 50,
            opacity: 0,
            duration: 0.8,
            delay: index * 0.1,
            ease: "back.out(1.5)"
        });
    });

    // 8. 旋轉徽章 (隨滾動旋轉)
    gsap.to(".js-rotate-scroll", {
        rotation: 360,
        ease: "none",
        scrollTrigger: {
            trigger: "body",
            start: "top top",
            end: "bottom bottom",
            scrub: 2
        }
    });

    // 9. 線條生長
    gsap.utils.toArray('.js-line-grow').forEach(line => {
        gsap.from(line, {
            scrollTrigger: { trigger: line, ...revealConfig },
            width: 0,
            duration: 1.2,
            ease: "power3.inOut"
        });
    });

    // 10. 全幅背景視差
    gsap.to(".js-parallax-bg", {
        backgroundPosition: "50% 100%",
        ease: "none",
        scrollTrigger: {
            trigger: ".parallax-banner",
            start: "top bottom",
            end: "bottom top",
            scrub: true
        }
    });

    // 11. 磁吸按鈕效果 (UX Micro-interaction)
    const magnets = document.querySelectorAll('.js-magnetic');
    magnets.forEach((magnet) => {
        magnet.addEventListener('mousemove', (e) => {
            const rect = magnet.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            gsap.to(magnet, {
                x: x * 0.2,
                y: y * 0.2,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        magnet.addEventListener('mouseleave', () => {
            gsap.to(magnet, {
                x: 0,
                y: 0,
                duration: 0.8,
                ease: "elastic.out(1, 0.4)"
            });
        });
    });

    // 12. 平滑滾動錨點
    document.querySelectorAll('.js-scroll-to').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const target = btn.getAttribute('href');
            gsap.to(window, {
                scrollTo: { y: target, offsetY: 50 },
                duration: 1.2,
                ease: "power3.inOut"
            });
        });
    });

});
</script>
@endpush
