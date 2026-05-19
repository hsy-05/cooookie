<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">

    <title>{{ $pageTitle ?? '' }}</title>

    <meta property="og:title" content="{{ $pageTitle ?? '' }}">
    <meta name="description" content="@yield('meta_description', config('site.site_meta_description') ?? '')">

    <meta name="keywords" content="@yield('meta_keywords', config('site.site_meta_keywords') ?? '')">

    <meta property="og:description" content="@yield('meta_description', config('site.site_meta_description') ?? '')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    <meta property="og:image" content="{{ asset('images/og-default.jpg') }}">

    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}">
   {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&family=Rubik:wght@500;600;700&family=Alice&display=swap" rel="stylesheet">

    {{-- Libs --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
    {{-- @vite(['resources/css/app.scss', 'resources/js/app.js']) --}}

    @stack('styles')
</head>

<body>
    {{-- 頁首 (Sticky + Glassmorphism) --}}
    <header class="site-header js-header">
        <h1 class="ele-hidden">{{ $currentTitle }}</h1>
        <div class="container header-inner">
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE">
                </a>
            </div>

            <!-- 主選單 (電腦版和手機版共用) -->
            <nav class="main-nav js-main-nav">
                <ul class="nav-list">
                    <li><a href="{{ url('/about') }}"
                            class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">關於我們</a></li>
                    <li><a href="{{ url('/product') }}"
                            class="nav-link {{ request()->routeIs('product.index') ? 'active' : '' }}">美味餅乾</a></li>
                    <li><a href="{{ url('/news') }}"
                            class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a></li>
                    <li><a href="{{ url('/contact') }}"
                            class="nav-link {{ request()->routeIs('contact.index') ? 'active' : '' }}">聯絡我們</a></li>
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
                <a href="{{ url('/product') }}">產品一覽</a>
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
    {{-- 全站核心交互 (已移至外部檔案以維護可讀性) --}}

    {{-- @vite(['resources/js/app.js']) --}}
    <script src="{{ asset('js/frontend/common.js') }}"></script>


    @stack('scripts')
</body>

</html>
