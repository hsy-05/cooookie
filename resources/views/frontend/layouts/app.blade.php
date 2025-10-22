<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'COOOOKIE') - COOOOKIE</title>

    {{-- 網站 icon --}}
    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}" />
    {{-- Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    {{-- Swiper 或其他輪播（目前未使用，預留） --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    {{-- AOS 動畫庫（保留但不依賴，作為 fallback） --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
    {{-- 自訂樣式 --}}
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}" />

    {{-- 引入其他頁面專屬的 CSS --}}
    @stack('styles')
</head>

<body>
    {{-- 共用 Header --}}
    <header class="header site-header">
        <div class="header-inner">
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE Logo" />
                </a>
            </div>
            <nav class="site-nav" id="site-nav">
                <a href="{{ url('/about') }}"
                    class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">關於我們</a>
                <a href="{{ url('/news') }}" class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
            <div class="hamburger" id="hamburger">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>
        </div>
    </header>

    {{-- 主內容區域，內容於各子頁面填充 --}}
    <main class="main-content" data-aos="fade-up" data-aos-duration="800">
        @yield('content')
    </main>

    {{-- 頁尾 --}}
    <footer class="site-footer" id="footer">
        <div class="container">
            <p class="mb-1 small">© {{ date('Y') }} COOOOKIE - All rights reserved.</p>
            <p class="footer-links small">
                <a href="{{ url('/privacy') }}">隱私政策</a> |
                <a href="{{ url('/terms') }}">使用條款</a>
            </p>
        </div>
    </footer>

    {{-- Scroll to Top 返回頂部按鈕 --}}
    <a class="scroll-top ani-fadeleft" data-scrollview></a>

    {{-- 引入外部 JS 與依賴 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Swiper（如有輪播再啟用） --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    {{-- AOS 動畫庫（fallback） --}}
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mobile-detect@1.4.5/mobile-detect.min.js"></script>
    <script>
        AOS.init(); // 初始化 AOS 動畫（僅 fallback，用 GSAP 取代大部分）
    </script>

    <script>
        var $win = $(window),
            $winW = window.innerWidth,
            $winH = window.innerHeight,
            $html = $('html'),
            $body = $('body');

        var $header = $('#header'),
            headerH = $header.height(),
            $nav = $('.nav-menu'),
            $menu = $('.menu-main'),
            $menuLink = $('.menu-main > li'),
            $navBtn = $('.nav-switch'),
            $goTop = $('.scroll-top');

        $win.on("resize", function() {
            $winW = window.innerWidth;
            $winH = window.innerHeight;
        });

        function highFull() {
            var vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        highFull();
        $win.resize(highFull);

        // Mobile Detect
        var $md = new MobileDetect(window.navigator.userAgent);
        if ($md.mobile()) $body.addClass('mb');
        else $body.addClass('pc');

        // {{-- 導覽與漢堡選單邏輯 --}}
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const nav = document.querySelector('.site-nav');

            if (hamburger && nav) {
                hamburger.addEventListener('click', () => {
                    hamburger.classList.toggle('active');
                    nav.classList.toggle('open');
                });
            }
        });

        // 返回頂部按鈕顯示/隱藏邏輯
        $win.on('scroll', function() {
            var $sctop = $(this).scrollTop();
            var $footer_top = $('#footer').offset().top;
            if ($win.scrollTop() + $win.innerHeight() > $footer_top + $('.scroll-top').height()) {
                $('.scroll-top').removeClass('dark');
            } else {
                $('.scroll-top').addClass('dark');
            }
            if ($sctop > 0) {
                $('.scroll-top').addClass('is-show');
            } else {
                $('.scroll-top').removeClass('is-show');
            }
        }).trigger('resize');

        $('.scroll-top').click(function() {
            $('html, body').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
    </script>

    {{-- GSAP 動畫庫（升級到最新版，包含 ScrollTrigger） --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    {{-- Lottie 動畫（用於手寫或 icon 複雜動畫） --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    @stack('scripts')
</body>

</html>
