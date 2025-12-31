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
                    <li><a href="{{ url('/about') }}" class="nav-link">關於我們</a></li>
                    <li><a href="{{ url('/products') }}" class="nav-link">美味餅乾</a></li>
                    <li><a href="{{ url('/news') }}" class="nav-link">最新消息</a></li>
                    <li><a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a></li>
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

    {{-- 回到頂部按鈕 (設計感圓環) --}}
    <button id="backToTop" class="back-to-top" aria-label="Top">
    <svg class="progress-ring" width="60" height="60">
        <!-- 背景圓環 -->
        <circle class="progress-ring__background" stroke="currentColor" fill="transparent" r="28" cx="30" cy="30" />

        <!-- 進度圓環 -->
        <circle class="progress-ring__circle" stroke="currentColor" fill="transparent" r="28" cx="30" cy="30" />
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
        // 🚨 將所有程式碼移動到 'load' 事件監聽器中
        window.addEventListener('load', () => {

            // 1. Lenis Smooth Scroll
            const lenis = new Lenis({
                duration: 1.2,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                direction: 'vertical',
                gestureDirection: 'vertical',
                smooth: true,
                mouseMultiplier: 1,
                smoothTouch: false,
                touchMultiplier: 2,
            });

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // 👇 GSAP 註冊應在函式庫載入後
            gsap.registerPlugin(ScrollTrigger);

            // 2. Mobile Menu Logic
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.site-header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // 主選單
            const hamburger = document.querySelector('.js-hamburger');
            const mainNav = document.querySelector('.js-main-nav');
            const navLinks = document.querySelectorAll('.nav-link');

            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('is-active');
                mainNav.classList.toggle('is-open');

                // 鎖定/解鎖背景滾動
                document.body.style.overflow = mainNav.classList.contains('is-open') ? 'hidden' : '';

                // 菜單動畫
                if (mainNav.classList.contains('is-open')) {
                    gsap.from(navLinks, {
                        y: 30,
                        opacity: 0,
                        duration: 0.6,
                        stagger: 0.15,
                        ease: 'power2.out'
                    });
                }
            });

            // 手機板-關閉主選單
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) {
                        hamburger.classList.remove('is-active');
                        mainNav.classList.remove('is-open');
                        document.body.style.overflow = '';
                    }
                });
            });

            // 3. Back to Top with Progress Ring
            const backToTopBtn = document.getElementById('backToTop');
            const circle = document.querySelector('.progress-ring__circle');
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;

            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            circle.style.strokeDashoffset = circumference;

            function setProgress(percent) {
                const offset = circumference - (percent / 100) * circumference;
                circle.style.strokeDashoffset = offset;
            }

            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY;
                const docHeight = document.body.scrollHeight - window.innerHeight;
                const scrollPercent = (scrollTop / docHeight) * 100;

                setProgress(scrollPercent);

                if (scrollTop > 300) {
                    backToTopBtn.classList.add('is-visible');
                } else {
                    backToTopBtn.classList.remove('is-visible');
                }
            });

            backToTopBtn.addEventListener('click', () => {
                lenis.scrollTo(0);
            });


            // 4. 視差圖片效果
            gsap.utils.toArray('.js-parallax-img').forEach(container => {
                const img = container.querySelector('img');
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
            });

        }); // End of 'load' event listener
    </script>
    @stack('scripts')
</body>

</html>
