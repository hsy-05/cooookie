<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'COOOOKIE') - 職人手作甜點</title>
    <meta name="description" content="COOOOKIE - 2025 高級手作甜點。堅持純粹食材，打造不完美的完美滋味。">
    <meta property="og:title" content="@yield('title', 'COOOOKIE')">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;600&family=Noto+Serif+TC:wght@400;700&display=swap"
        rel="stylesheet">

    {{-- Libs --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">

    @stack('styles')
</head>

<body>
    {{-- 全域雜訊紋理 --}}
    <div class="global-noise"></div>

    {{-- 頁首 (Sticky + Glassmorphism) --}}
    <header class="site-header js-header">
        <div class="container header-inner">
            <h1 class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE">
                </a>
            </h1>

            <!-- 主選單 (電腦版和手機版共用) -->
            <nav class="main-nav js-main-nav">
                <ul class="nav-list">
                    <li><a href="{{ url('/about') }}"
                            class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">關於我們</a></li>
                    <li><a href="{{ url('/products') }}"
                            class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}">美味餅乾</a></li>
                    <li><a href="{{ url('/news') }}"
                            class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a></li>
                    <li><a href="{{ url('/contact') }}"
                            class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">聯絡我們</a></li>
                </ul>
            </nav>

            <!-- 漢堡按鈕 -->
            <div class="hamburger js-hamburger">
                <span></span><span></span><span></span>
            </div>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    {{-- 頁尾 --}}
    <footer class="site-footer">
        <div class="container">
            <div class="footer-logo">
                <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE" width="250" height="40">
            </div>

            <div class="footer-nav mb-4">
                <a href="{{ url('/about') }}">關於我們</a>
                <a href="{{ url('/products') }}">產品一覽</a>
                <a href="{{ url('/news') }}">最新消息</a>
                <a href="{{ url('/faq') }}">常見問題</a>
                <a href="{{ url('/contact') }}">聯絡我們</a>
            </div>

            <div class="footer-social mb-5">
                <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="Line"><i class="bi bi-line"></i></a>
            </div>

            <div class="footer-info">
                <p>台南市中西區忠義路二段123號 | 電話：(06) 222-3333</p>
                <p>營業時間：週一至週日 10:00-19:00</p>
                <p class="copyright mt-3">© {{ date('Y') }} COOOOKIE. 保留所有權利。</p>
            </div>
        </div>
    </footer>

    <button id="backToTop" class="back-to-top" aria-label="Scroll to Top">
        <svg class="progress-ring" width="60" height="60" viewBox="0 0 60 60">
            <circle class="progress-ring__background" cx="30" cy="30" r="28" fill="transparent"
                stroke-width="4" />
            <circle class="progress-ring__circle" cx="30" cy="30" r="28" fill="transparent"
                stroke-width="4" />
        </svg>
        <span class="btt-icon">↑</span>
    </button>


    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/studio-freight/lenis@1.0.29/bundled/lenis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
    <script>
        /**
         * 【COOOOKIE】全站交互邏輯
         * 功能：
         * 1. Lenis 平滑滾動
         * 2. Header 滾動效果
         * 3. 行動版選單 (漢堡選單)
         * 4. 回頂端按鈕 + 進度圓環
         * 5. GSAP 視差效果
         * 6. Resize 監聽，避免 responsive 跑版
         */
        window.addEventListener('load', () => {

            // --- 1. 初始化 Lenis 平滑滾動 ---
            const lenis = new Lenis({
                duration: 1.2, // 滾動動畫持續時間
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // 緩動函數
                direction: 'vertical', // 垂直滾動
                gestureDirection: 'vertical', // 手勢方向
                smooth: true, // 開啟平滑
                mouseMultiplier: 1, // 滑鼠滾輪靈敏度
                smoothTouch: false, // 手機觸控維持原生手感
                touchMultiplier: 2 // 觸控靈敏度
            });

            // 驅動 Lenis 的動畫幀
            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // --- 2. GSAP 插件註冊 ---
            gsap.registerPlugin(ScrollTrigger);

            // --- 3. UI 元素選取 ---
            const header = document.querySelector('.site-header');
            const hamburger = document.querySelector('.js-hamburger');
            const mainNav = document.querySelector('.js-main-nav');
            const navLinks = document.querySelectorAll('.nav-link');
            const backToTopBtn = document.getElementById('backToTop');
            const circle = document.querySelector('.progress-ring__circle');

            // --- 4. 初始化進度圓環 ---
            let circumference;
            if (circle) {
                const radius = circle.r.baseVal.value;
                circumference = 2 * Math.PI * radius;
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;
            }

            // --- 5. 滾動事件 (整合 Lenis) ---
            lenis.on('scroll', (e) => {
                const scrollTop = e.animatedScroll; // Lenis 計算後滾動距離
                const docHeight = document.body.scrollHeight - window.innerHeight;
                const scrollPercent = (scrollTop / docHeight) * 100;

                // A. Header 滾動效果
                scrollTop > 50 ? header.classList.add('scrolled') : header.classList.remove('scrolled');

                // B. 回頂端按鈕顯示
                if (backToTopBtn) {
                    scrollTop > 300 ? backToTopBtn.classList.add('is-visible') : backToTopBtn.classList
                        .remove('is-visible');
                }

                // C. 更新進度圓環
                if (circle) {
                    const offset = circumference - (scrollPercent / 100) * circumference;
                    circle.style.strokeDashoffset = offset;
                }
            });

            // --- 6. 行動版選單切換邏輯 ---
            if (hamburger && mainNav) {
                const closeMenu = () => {
                    mainNav.classList.remove('is-open');
                    hamburger.classList.remove('is-active');
                    document.body.style.overflow = '';
                };

                hamburger.addEventListener('click', () => {
                    const isOpen = mainNav.classList.toggle('is-open');
                    hamburger.classList.toggle('is-active');
                    document.body.style.overflow = isOpen ? 'hidden' : '';

                    // 選單開啟動畫
                    if (isOpen) {
                        gsap.from(navLinks, {
                            y: 30,
                            opacity: 0,
                            duration: 0.6,
                            stagger: 0.15,
                            ease: 'power2.out'
                        });
                    }
                });

                // 點擊選單連結自動關閉 (手機/平板)
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 1024) closeMenu();
                    });
                });

                // --- 7. Resize 偵測 ---
                window.addEventListener('resize', () => {
                    if (window.innerWidth > 1024) {
                        // 桌面版一定要關閉手機選單，避免跑版
                        closeMenu();
                    }
                });
            }

            // --- 8. 回頂端按鈕點擊 ---
            if (backToTopBtn) {
                backToTopBtn.addEventListener('click', () => lenis.scrollTo(0));
            }

            // --- 9. GSAP 視差圖片效果 ---
            const parallaxImages = gsap.utils.toArray('.js-parallax-img');
            parallaxImages.forEach(container => {
                const img = container.querySelector('img');
                if (img) {
                    gsap.to(img, {
                        yPercent: 20,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: container,
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: true
                        }
                    });
                }
            });

        }); // End of Window Load
    </script>

    @stack('scripts')
</body>

</html>
