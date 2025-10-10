@extends('frontend.layouts.app')

@section('title', '關於我們 - COOOOKIE')

@push('styles')
    {{-- Font Awesome 5 引入，確保圖標顯示正常 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    {{-- 引入關於我們頁面專屬的 CSS --}}
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
@endpush

@section('content')
    {{--
        Hero Section: 頂部橫幅區塊 - 品牌形象與沉浸式體驗
        設計理念：大字標題用手寫感字體 (Pacifico)，副標語柔和；背景插畫風（假設 images/hero-bg.jpg 是插畫餅乾/廚房）；元素如漂浮餅乾。
        互動與動畫：用 GSAP 實現 Parallax (背景慢速移動)；主標題分段滑入 + 輕微跳動 (elastic ease)；副標淡入從下方滑入；粒子浮動 (yoyo 上下)。
        為何這樣：取代 AOS 以 GSAP Timeline 確保順序連貫；Parallax 加互動感；RWD：在手機減弱動畫強度。
        圖片尺寸：電腦版 1920x530，手機版 800x530，已在 CSS 中處理不同背景圖。
    --}}
    <div class="about-hero-section" id="hero-section">
        {{-- 背景圖容器，用於 Parallax --}}
        <div class="hero-bg"></div>
        {{-- 輕量粒子效果容器 (餅乾碎屑或光點，GSAP 動畫控制) --}}
        <div class="hero-particles">
            <div class="hero-particle" data-gsap="particle"></div>
            <div class="hero-particle" data-gsap="particle"></div>
            <div class="hero-particle" data-gsap="particle"></div>
            <div class="hero-particle" data-gsap="particle"></div>
            <div class="hero-particle" data-gsap="particle"></div>
        </div>
        <div class="hero-overlay"></div>

        <div class="container text-center hero-content">
            {{-- 主標題：用手寫字體，GSAP 滑入 + 跳動 --}}
            <h1 class="hero-title">關於我們</h1>

            {{-- 副標題：GSAP 從下方滑入 --}}
            <p class="hero-subtitle">
                每一塊餅乾，都來自溫暖的雙手與心意
            </p>

            {{-- 新增 CTA 按鈕：行動呼籲，GSAP 滑入 + hover ripple --}}
            <div class="hero-cta">
                <a href="{{ url('/products') }}" class="btn-explore" title="探索我們的旅程">探索我們的旅程</a>
            </div>
        </div>
    </div>

    {{--
        區塊連接 Divider: 曲線波浪 SVG，用於 hero 和 品牌故事 之間連接
        用途：讓區塊感覺連貫，不是 abrupt 切換；GSAP 滾動觸發波浪伸展動畫。
        為何 SVG：輕量、可動畫 clip-path；inline SVG 避免額外請求。
    --}}
    <div class="section-divider bg13-divider"></div>

    {{--
        品牌故事 (Intro Section): 左右兩欄布局 - 圖片與文字交錯
        設計理念：左圖右文，圖片插畫風 (過程從爐到包裝)；背景紙紋 CSS (texture overlay)。
        互動與動畫：圖片 Parallax (mousemove 傾斜，用 GSAP)；文字段落逐行顯示 (GSAP split + stagger)；hover 圖片放大 + 光暈。
        為何這樣：增加互動感；ScrollTrigger 確保滾動時 reveal，連貫到下一區。
        RWD：在小螢幕上，垂直堆疊，減弱 Parallax。
    --}}
    <section class="about-intro-section" id="intro-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 order-md-2">
                    <div class="image-wrapper" data-gsap="parallax-image">
                        <picture>
                            <source srcset="{{ asset('images/about-intro-1.webp') }}" type="image/webp">
                            <img src="{{ asset('images/about-intro-1.png') }}" alt="品牌故事圖片" loading="lazy" width="800"
                                height="600">
                        </picture>
                    </div>
                </div>
                <div class="col-md-6 order-md-1 text-content">
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

    {{--
        區塊連接 Divider: 餅乾咬痕 SVG，用於 品牌故事 和 我們的堅持 之間
        用途：視覺連接，GSAP 動畫咬痕擴張；clip-path 讓下一區從咬痕「露出」。
    --}}
    <div class="section-divider bite-divider">
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path d="M0,50 Q360,0 720,50 Q1080,100 1440,50 L1440,100 L0,100 Z" fill="#f8f4f0" data-gsap="bite-path"></path>
            {{-- 咬痕曲線 --}}
        </svg>
    </div>

    {{--
        我們的堅持 (Values Section): 橫向 Timeline 流程 - 核心價值展示
        設計理念：改 grid 為 flex 橫向 (Timeline 式，從選料到包裝)；每個項目 icon 用 Lottie 或 SVG。
        互動與動畫：ScrollTrigger reveal + stagger 滑入；hover icon 放大/顏色變 (GSAP)；黏性滾動 (pin 區塊)。
        為何這樣：模擬流程連貫感；RWD：手機垂直，桌面橫向 scroll。
    --}}
    <section class="about-values-section" id="values-section">
        {{-- 背景裝飾性波浪 - 增加區塊間的視覺流動感 --}}
        <div class="values-bg-wave"></div>

        <div class="container">
            <div class="text-center">
                <h2 class="section-heading">我們的堅持</h2>
                <p class="section-subheading">
                    COOOOKIE 秉持三大核心價值，為您帶來最美好的烘焙體驗。
                </p>
            </div>
            <div class="feature-timeline d-flex"> {{-- 改為 flex 橫向 Timeline --}}
                {{-- 項目 1: 嚴選食材 --}}
                <div class="feature-item" data-gsap="timeline-item">
                    <div class="feature-icon" data-lottie-path="{{ asset('lottie/seedling.json') }}"> {{-- Lottie JSON 路徑 --}}
                        <i class="fas fa-seedling fallback-icon"></i> {{-- fallback 如果無 Lottie --}}
                    </div>
                    <h3 class="feature-title">嚴選食材</h3>
                    <p class="feature-desc">
                        我們堅持使用來自世界各地的頂級原料，從麵粉、奶油到巧克力，每一份食材都經過嚴格篩選，確保天然、新鮮、無添加，為您呈現最純粹的風味。
                    </p>
                </div>
                {{-- 項目 2: 匠心手作 --}}
                <div class="feature-item" data-gsap="timeline-item">
                    <div class="feature-icon" data-lottie-path="{{ asset('lottie/hands.json') }}">
                        <i class="fas fa-hands-helping fallback-icon"></i>
                    </div>
                    <h3 class="feature-title">匠心手作</h3>
                    <p class="feature-desc">
                        每一塊餅乾都由經驗豐富的烘焙師傅手工製作，從揉麵、塑形到烘烤，每一個步驟都充滿了匠人精神與對細節的執著，只為成就獨一無二的美味。
                    </p>
                </div>
                {{-- 項目 3: 品質保證 --}}
                <div class="feature-item" data-gsap="timeline-item">
                    <div class="feature-icon" data-lottie-path="{{ asset('lottie/shield.json') }}">
                        <i class="fas fa-shield-alt fallback-icon"></i>
                    </div>
                    <h3 class="feature-title">品質保證</h3>
                    <p class="feature-desc">
                        我們嚴格控管生產流程，定期進行SGS檢測，確保每一批產品都符合最高衛生標準與品質要求，讓您吃得安心、吃得放心。
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{--
        區塊連接 Divider: 曲線波浪，用於 我們的堅持 和 品牌願景 之間
        用途：漸變顏色，GSAP 滾動模糊轉場。
    --}}
    <div class="section-divider wave-divider">
        <svg viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path d="M0,100 C360,0 1080,0 1440,100 L1440,100 L0,100 Z" fill="#f8f4f0"></path> {{-- 反向波浪 --}}
        </svg>
    </div>

    {{--
        品牌願景 (Vision Section): 全版背景布局 - 圖片與文字
        設計理念：全版背景圖；手寫文字用 SVG 路徑；CTA 呼籲。
        互動與動畫：背景滾動縮小/模糊 (GSAP ScrollTrigger)；手寫文字 stroke 逐步寫出；CTA hover ripple (GSAP scale/opacity)。
        為何這樣：情感結尾，互動增加停留時間；RWD：背景 cover。
    --}}
    <section class="about-vision-section" id="vision-section">
        <div class="container">
            <div class="row align-items-center flex-md-row-reverse">
                <div class="col-md-6">
                    <div class="image-wrapper">
                        <picture>
                            <source srcset="{{ asset('images/about-vision-1.webp') }}" type="image/webp">
                            <img src="{{ asset('images/about-vision-1.jpg') }}" alt="COOOOKIE 願景圖片" loading="lazy"
                                width="800" height="600">
                        </picture>
                    </div>
                </div>
                <div class="col-md-6 text-content">
                    <h2 class="section-heading">品牌願景</h2>
                    <p class="lead">
                        COOOOKIE 致力於成為您生活中不可或缺的甜蜜夥伴。我們期望透過每一塊用心製作的餅乾，傳遞幸福的滋味，豐富您的日常。
                    </p>
                    <p>
                        未來，我們將持續創新，拓展更多元的產品線，並積極參與社區活動，回饋社會。讓 COOOOKIE 的溫暖與美味，散播到世界的每一個角落，成為全球家庭的甜蜜記憶。
                    </p>
                    {{-- 手寫文字 SVG --}}
                    <svg class="handwritten-svg" viewBox="0 0 400 100">
                        <path id="handwrite-path" d="M0 50 Q100 0 200 50 Q300 100 400 50" fill="none" stroke="#000"
                            stroke-width="2" stroke-dasharray="1000" stroke-dashoffset="1000"></path>
                        {{-- 路徑動畫 --}}
                        <text fill="#000">
                            <textPath xlink:href="#handwrite-path">手寫的一句話</textPath> {{-- 替換成創辦人手寫文字 --}}
                        </text>
                    </svg>
                    {{-- CTA --}}
                    <div class="vision-cta">
                        <a href="{{ url('/contact') }}" class="btn-contact" title="聯絡我們">聯絡我們</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{--
        試吃申請 (Tasting Section): 全屏背景圖 - 呼籲行動區塊
        設計理念：維持原有，但加 GSAP Parallax 和 ripple。
        互動與動畫：增強 icon 脈動；按鈕 ripple。
    --}}
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
                    我們有多樣化的點心、中式與西式的喜餅、活動餐盒、手工特色麵包，<br>
                    可以滿足您的味蕾，尋找美味的甜點，歡迎申請試吃。
                </p>
                <div class="g-btn-wrap">
                    <a href="{{ url('/contact') }}" title="按我申請" class="g-btn-link">按我申請</a>
                </div>
            </div>
        </div>
        <div class="i-tasting__bg"><img src="{{ asset('images/taste-pic4.jpg') }}" alt="(圖)*"></div>
    </section>

@endsection

@push('scripts')
    <script>
        // GSAP 初始化與動畫模組
        gsap.registerPlugin(ScrollTrigger); // 註冊 ScrollTrigger 插件，用於滾動觸發

        document.addEventListener('DOMContentLoaded', function() {
            // 模組 1: Hero Banner 動畫
            function initHeroAnimations() {
                // Parallax 背景：滾動時慢速移動 y 軸
                gsap.to('.hero-bg', {
                    y: '20%', // 移動 20% 高度
                    ease: 'none',
                    scrollTrigger: {
                        trigger: '#hero-section',
                        scrub: true, // 跟隨滾動
                        start: 'top top',
                        end: 'bottom top'
                    }
                });

                // 主標題滑入 + 跳動
                gsap.from('.hero-title', {
                    y: 100,
                    opacity: 0,
                    duration: 1,
                    ease: 'elastic.out(1, 0.3)', // 輕微跳動
                    delay: 0.5
                });

                // 副標從下方滑入
                gsap.from('.hero-subtitle', {
                    y: 50,
                    opacity: 0,
                    duration: 0.8,
                    ease: 'power2.out',
                    delay: 1
                });

                // 粒子浮動：隨機上下 yoyo
                gsap.utils.toArray('[data-gsap="particle"]').forEach((particle, i) => {
                    gsap.to(particle, {
                        y: gsap.utils.random(-20, 20),
                        x: gsap.utils.random(-10, 10),
                        duration: gsap.utils.random(2, 4),
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                        delay: i * 0.2 // stagger 延遲
                    });
                });

                // CTA hover ripple：scale + opacity 波紋
                const cta = document.querySelector('.btn-explore');
                cta.addEventListener('mouseenter', () => {
                    gsap.to(cta, {
                        scale: 1.1,
                        duration: 0.3,
                        ease: 'power1.out'
                    });
                });
                cta.addEventListener('mouseleave', () => {
                    gsap.to(cta, {
                        scale: 1,
                        duration: 0.3,
                        ease: 'power1.out'
                    });
                });
            }

            // 模組 2: 品牌故事 動畫
            function initIntroAnimations() {
                // 圖片 Parallax：mousemove 傾斜
                const imageWrapper = document.querySelector('[data-gsap="parallax-image"]');
                imageWrapper.addEventListener('mousemove', (e) => {
                    const rect = imageWrapper.getBoundingClientRect();
                    const x = (e.clientX - rect.left) / rect.width - 0.5;
                    const y = (e.clientY - rect.top) / rect.height - 0.5;
                    gsap.to(imageWrapper, {
                        rotationY: x * 10,
                        rotationX: y * 10,
                        duration: 0.5,
                        ease: 'power1.out'
                    });
                });
                imageWrapper.addEventListener('mouseleave', () => {
                    gsap.to(imageWrapper, {
                        rotationY: 0,
                        rotationX: 0,
                        duration: 0.5
                    });
                });

                // 文字逐行顯示：stagger from
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
            }

            // 模組 3: 我們的堅持 動畫
            function initValuesAnimations() {
                // Timeline stagger reveal：滾動時逐項滑入
                gsap.utils.toArray('[data-gsap="timeline-item"]').forEach((item, i) => {
                    gsap.from(item, {
                        x: -50,
                        opacity: 0,
                        duration: 0.8,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: item,
                            start: 'top 80%',
                            stagger: 0.2 // 流程感 stagger
                        }
                    });

                    // Hover icon 放大 + 顏色變
                    const icon = item.querySelector('.feature-icon');
                    icon.addEventListener('mouseenter', () => {
                        gsap.to(icon, {
                            scale: 1.2,
                            color: '#ff69b4',
                            duration: 0.3
                        }); // 粉紅變色
                    });
                    icon.addEventListener('mouseleave', () => {
                        gsap.to(icon, {
                            scale: 1,
                            color: 'initial',
                            duration: 0.3
                        });
                    });
                });

                // Lottie 初始化：每個 icon 如果有 JSON
                gsap.utils.toArray('[data-lottie-path]').forEach((el) => {
                    lottie.loadAnimation({
                        container: el,
                        renderer: 'svg',
                        loop: true,
                        autoplay: true,
                        path: el.dataset.lottiePath
                    });
                });

                // 黏性滾動：pin 整個 section，橫向 scroll (如果桌面)
                // if (window.innerWidth > 768) {
                //     ScrollTrigger.create({
                //         trigger: '#values-section',
                //         pin: true,
                //         start: 'top top',
                //         end: '+=100%',
                //         scrub: true,
                //         // prevent snapping back
                //         pinSpacing: true
                //     });
                // }
            }

            // 模組 4: 品牌願景 動畫
            function initVisionAnimations() {
                // 區塊從模糊變清晰
                gsap.fromTo('#vision-section', {
                    filter: 'blur(8px)',
                    opacity: 0.6
                }, {
                    filter: 'blur(0px)',
                    opacity: 1,
                    duration: 0.5,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: '#vision-section',
                        start: 'top 80%',
                        toggleActions: 'play none none reverse'
                    }
                });

                // 手寫路徑動畫
                gsap.to('#handwrite-path', {
                    strokeDashoffset: 0,
                    duration: 3,
                    ease: 'power1.inOut',
                    scrollTrigger: {
                        trigger: '.handwritten-svg',
                        start: 'top 80%',
                    }
                });

                // CTA Ripple
                const cta = document.querySelector('.btn-contact');
                cta.addEventListener('mouseenter', () => {
                    gsap.to(cta, {
                        scale: 1.05,
                        duration: 0.3,
                        boxShadow: '0 0 20px rgba(255,105,180,0.5)'
                    });
                });
                cta.addEventListener('mouseleave', () => {
                    gsap.to(cta, {
                        scale: 1,
                        boxShadow: 'none',
                        duration: 0.3
                    });
                });
            }

            // 模組 5: Divider 連接動畫
            function initDividers() {
                gsap.utils.toArray('.section-divider').forEach((divider) => {
                    gsap.from(divider, {
                        scaleY: 0,
                        duration: 1,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: divider,
                            start: 'top bottom',
                            toggleActions: 'play none none reverse'
                        }
                    });
                });

                // 咬痕特定動畫：path 擴張
                gsap.to('[data-gsap="bite-path"]', {
                    attr: {
                        d: 'M0,50 Q360,0 720,50 Q1080,100 1440,50 L1440,100 L0,100 Z'
                    }, // 初始 d
                    duration: 1.5,
                    ease: 'elastic.out(1,0.5)',
                    scrollTrigger: {
                        trigger: '.bite-divider',
                        start: 'top 80%'
                    }
                });
            }

            // 初始化所有模組
            initHeroAnimations();
            initIntroAnimations();
            initValuesAnimations();
            initVisionAnimations();
            initDividers();

            // 全局性能：refresh ScrollTrigger on resize
            window.addEventListener('resize', () => ScrollTrigger.refresh());
        });

        // 原有 ripple 按鈕邏輯（增強 tasting 區）
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.g-btn-link');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    this.classList.remove('active');
                    void this.offsetWidth;
                    this.classList.add('active');
                });
                button.addEventListener('transitionend', function() {
                    this.classList.remove('active');
                });
            });
        });
    </script>
@endpush
