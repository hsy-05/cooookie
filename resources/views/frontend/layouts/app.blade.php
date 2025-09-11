<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'COOOOKIE') - 公司名稱</title>

    {{-- 網站小圖示 --}}
    <link href="{{ asset('favicons/favicon.ico') }}" rel="icon" />
    {{-- Bootstrap & 前台樣式 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}" />
</head>

<body>
    {{-- Header --}}
    <header class="site-header">
        <div class="header-inner">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="logo" aria-label="回首頁">
                <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE Logo" />
            </a>

            {{-- 漢堡選單 --}}
            <div class="hamburger" id="hamburger" aria-label="開啟選單" aria-expanded="false" role="button" tabindex="0">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>

            {{-- 主選單 --}}
            <nav class="site-nav" id="site-nav" role="navigation" aria-label="主選單">
                <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/about') }}" class="nav-link">關於我們</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
        </div>
    </header>

    {{-- 主內容 --}}
    <main class="main-content">
        {{-- Banner 區塊 --}}
        @include('frontend.components.banner')

        {{-- 其他內容 --}}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="site-footer">
        <div class="container text-center small">
            © {{ date('Y') }} COOOOKIE - All rights reserved.
        </div>
    </footer>

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(function () {
            const header = $('.site-header');
            const nav = $('#site-nav');
            const hamburger = $('#hamburger');
            const headerHeight = header.outerHeight();

            // 捲動時 header 背景改變
            $(window).on('scroll', function () {
                if ($(this).scrollTop() > headerHeight) {
                    header.addClass('scrolled');
                } else {
                    header.removeClass('scrolled');
                }
            });

            // 手機漢堡選單開關
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

            // 點擊選單關閉
            nav.find('.nav-link').on('click', function () {
                if (hamburger.hasClass('active')) toggleMenu();
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
