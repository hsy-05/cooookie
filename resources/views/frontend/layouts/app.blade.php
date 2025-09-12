<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'COOOOKIE') - 公司名稱</title>
    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}" />
    {{-- Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
    {{-- Swiper 或別的輪播如果有用 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    {{-- AOS --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />
    {{-- 自訂樣式 --}}
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}" />
</head>

<body>
    {{-- 共用 Header --}}
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
                <a href="{{ route('news.index') }}"
                    class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/about') }}" class="nav-link">關於我們</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
        </div>
    </header>

    {{-- 如果這個頁面有 banner 節（首頁或消息列表等帶 banner 的頁面），才 render banner 區塊 --}}
    @hasSection('banner')
        <section class="banner-section">
            @yield('banner')
        </section>
    @endif

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

    {{-- JS 與依賴載入 --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init();
    </script>
    <script>
        $(function() {
            const header = $('.site-header');
            const nav = $('#site-nav');
            const hamburger = $('#hamburger');

            const headerHeight = header.outerHeight();
            let bannerHeight = $('.banner-section').outerHeight() || 0;

            // 頁面滾動時切換 header 樣式
            function checkScroll() {
                const scrollTop = $(window).scrollTop();
                if (scrollTop > bannerHeight * 0.5) {
                    header.addClass('scrolled');
                } else {
                    header.removeClass('scrolled');
                }
            }

            // 手機選單開關
            function toggleMenu(open = null) {
                const isOpen = hamburger.hasClass('active');

                const shouldOpen = open !== null ? open : !isOpen;

                hamburger.toggleClass('active', shouldOpen);
                nav.toggleClass('open', shouldOpen);
                hamburger.attr('aria-expanded', shouldOpen);

                // 如果開啟選單，禁止滾動背景
                $('body').toggleClass('no-scroll', shouldOpen);
            }

            // 初始化
            checkScroll();

            // 滾動與視窗大小變更時檢查 banner 高度與 header 樣式
            $(window).on('scroll resize', function() {
                bannerHeight = $('.banner-section').outerHeight() || 0;
                checkScroll();
            });

            // 漢堡點擊與鍵盤操作（Enter、空白鍵）
            hamburger.on('click keypress', function(e) {
                if (e.type === 'click' || e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleMenu();
                }
            });

            // 點擊 nav-link 時關閉手機選單
            nav.find('.nav-link').on('click', function() {
                if (hamburger.hasClass('active')) {
                    toggleMenu(false);
                }
            });

            // 點擊頁面其他地方時自動關閉手機選單
            $(document).on('click', function(e) {
                if (nav.hasClass('open') && !$(e.target).closest('#site-nav, #hamburger').length) {
                    toggleMenu(false);
                }
            });

            // 按下 ESC 關閉選單
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && nav.hasClass('open')) {
                    toggleMenu(false);
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
