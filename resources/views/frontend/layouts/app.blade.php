<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'COOOOKIE') - 公司名稱</title>

    {{-- 網站 icon --}}
    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}" />

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />

    {{-- Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    {{-- Animate on Scroll --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />

    {{-- 自訂樣式 --}}
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}" />
</head>

<body>
    {{-- 頁首：LOGO + 導覽列 --}}
    <header class="site-header">
        <div class="header-inner container">
            <a href="{{ url('/') }}" class="logo" aria-label="回首頁">
                <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE Logo" />
            </a>

            <div class="hamburger" id="hamburger" aria-label="開啟選單" aria-expanded="false" role="button" tabindex="0">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>

            <nav class="site-nav" id="site-nav" role="navigation" aria-label="主選單">
                <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/about') }}" class="nav-link">關於我們</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
        </div>
    </header>

    {{-- 主內容區域 --}}
    <main class="main-content" data-aos="fade-up" data-aos-duration="800">
        @yield('content')
    </main>

    {{-- 頁尾 --}}
    <footer class="site-footer">
        <div class="container text-center small">
            <p class="mb-1">© {{ date('Y') }} COOOOKIE - All rights reserved.</p>
            <p class="footer-links">
                <a href="{{ url('/privacy') }}">隱私政策</a> |
                <a href="{{ url('/terms') }}">使用條款</a>
            </p>
        </div>
    </footer>

    {{-- JS：基本依賴 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Swiper --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    {{-- AOS 動畫庫 --}}
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init(); // 初始化 Animate on Scroll
    </script>

    {{-- 導覽與漢堡選單邏輯 --}}
    <script>
        $(function () {
            const header = $('.site-header');
            const nav = $('#site-nav');
            const hamburger = $('#hamburger');
            const headerHeight = header.outerHeight();

            // 捲動時變背景
            $(window).on('scroll', function () {
                header.toggleClass('scrolled', $(this).scrollTop() > headerHeight);
            });

            function toggleMenu() {
                hamburger.toggleClass('active');
                nav.toggleClass('open');
                hamburger.attr('aria-expanded', hamburger.hasClass('active'));
            }

            hamburger.on('click keypress', function (e) {
                if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleMenu();
                }
            });

            nav.find('.nav-link').on('click', function () {
                if (hamburger.hasClass('active')) toggleMenu();
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
