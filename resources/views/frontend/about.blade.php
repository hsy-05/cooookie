@extends('frontend.layouts.app')

@section('title', '關於我們 - COOOOKIE')

@push('styles')
    {{-- Font Awesome 圖示庫 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- 關於我們專屬 CSS --}}
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')

    {{-- ================= Hero Section ================= --}}
    <section class="about-hero-section">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="container text-center hero-content">
            <h1 class="hero-title">關於我們</h1>
            <p class="hero-subtitle">每一塊餅乾，都來自溫暖的雙手與心意</p>
            <div class="hero-cta">
                <a href="{{ url('/products') }}" class="btn-explore">探索我們的旅程</a>
            </div>
        </div>
    </section>

    {{-- ================= 品牌故事 ================= --}}
    <section class="about-intro-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6" data-gsap="text-line">
                    <div class="image-wrapper">
                        <img src="{{ asset('images/about-intro-1.jpg') }}" alt="品牌故事">
                    </div>
                </div>
                <div class="col-md-6 text-content">
                    <h2 class="section-heading">品牌故事</h2>
                    <p data-gsap="text-line">
                        COOOOKIE 的故事始於對烘焙的熱愛與對美味的追求。我們相信，每一塊餅乾都承載著溫暖與幸福，是連結人與人之間情感的橋樑。
                    </p>
                    <p data-gsap="text-line">
                        從嚴選天然食材到手工製作，每一個環節都傾注了我們的心血與堅持。我們不斷探索創新的口味與獨特的配方，只為將最純粹、最感動的滋味帶給您。COOOOKIE
                        不僅僅是餅乾，更是一種生活態度，一種對美好事物的嚮往。
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= 我們的堅持 ================= --}}
    <section class="about-values-section">
        <div class="values-bg"></div>
        <div class="container-fluid values-container">
            <div class="text-center mb-5">
                <h2 class="section-heading">我們的堅持</h2>
                <p class="section-subheading">從食材到包裝，每一步都代表我們對品質的承諾。</p>
            </div>

            <div class="values-scroll">
                {{-- 嚴選食材 --}}
                <div class="value-item">
                    <div class="value-card">
                        <div class="value-icon">
                            <img src="{{ asset('images/value-icon-1.png') }}" alt="嚴選食材">
                        </div>
                        <div class="value-text">
                            <h3>嚴選食材</h3>
                            <p>只採用天然無添加的頂級原料，確保每一口的純淨與安全。</p>
                        </div>
                    </div>
                </div>

                {{-- 匠心手作 --}}
                <div class="value-item">
                    <div class="value-card">
                        <div class="value-icon">
                            <img src="{{ asset('images/value-icon-2.png') }}" alt="匠心手作">
                        </div>
                        <div class="value-text">
                            <h3>匠心手作</h3>
                            <p>每一塊餅乾皆由職人親手烘焙，堅持最溫暖的手感。</p>
                        </div>
                    </div>
                </div>

                {{-- 品質保證 --}}
                <div class="value-item">
                    <div class="value-card">
                        <div class="value-icon">
                            <img src="{{ asset('images/value-icon-3.png') }}" alt="品質保證">
                        </div>
                        <div class="value-text">
                            <h3>品質保證</h3>
                            <p>經過嚴格檢驗與品管流程，只為給您最安心的美味。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= 品牌願景 ================= --}}
    <section class="about-vision-section">
        <div class="container text-center text-light">
            <h2 class="section-heading">品牌願景</h2>
            <p class="lead">COOOOKIE 將持續以溫暖與創意，成為每個家庭的甜蜜記憶。</p>
        </div>
    </section>

    {{-- ================= 試吃申請 ================= --}}
    <section class="i-tasting">
        <div class="i-tasting__overlay"></div>
        <div class="i-tasting__content">
            <div class="container-1440">
                <div class="i-tasting__icon" data-gsap="pulse-icon"></div>
                <h2 class="g__box-ti">
                    <span class="en">TASTE TESTING</span>
                    <span class="tw">試吃申請</span>
                </h2>
                <p class="i-tasting__text">
                    我們有多樣化的點心、喜餅與活動餐盒，<br>
                    歡迎申請試吃，感受幸福的滋味。
                </p>
                <div class="g-btn-wrap">
                    <a href="{{ url('/contact') }}" title="按我申請" class="g-btn-link">按我申請</a>
                </div>
            </div>
        </div>
        <div class="i-tasting__bg">
            <img src="{{ asset('images/taste-pic4.jpg') }}" alt="試吃申請背景圖">
        </div>
    </section>

@endsection

@push('scripts')
    <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
    <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
    <script>
        gsap.registerPlugin(ScrollTrigger);

        /* ===========================================================
         * ✅ 1. 修正「我們的堅持」重複觸發問題
         * =========================================================== */
        document.querySelectorAll('.value-item').forEach((item) => {
            ScrollTrigger.create({
                trigger: item,
                start: "top 85%",
                once: true,
                onEnter: () => {
                    item.classList.add('visible');
                    gsap.fromTo(item, {
                        y: 60,
                        opacity: 0
                    }, {
                        y: 0,
                        opacity: 1,
                        duration: 0.9,
                        ease: "power3.out"
                    });
                }
            });
        });

        /* ===========================================================
         * ✅ 2. 文字逐行顯示：stagger from
         * =========================================================== */

        //
        gsap.utils.toArray('[data-gsap="text-line"]').forEach((line, i) => {
            gsap.from(line, {
                opacity: 0,
                y: 20,
                duration: 0.6,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: line,
                    start: 'top 80%',
                    toggleActions: 'play none none reverse'
                },
                delay: i * 0.3 // 逐行 stagger
            });
        });
    </script>
@endpush
