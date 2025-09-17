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
        <div class="header-inner">
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COOOOKIE Logo" />
                </a>
            </div>
            <nav class="site-nav" id="site-nav"> {{-- 新增 id="site-nav" 以便 JS 抓取 --}}
                <a href="{{ route('news.index') }}"
                    class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/about') }}" class="nav-link">關於我們</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
            <div class="hamburger" id="hamburger">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </div>
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

    {{-- JS 與依賴載入 --}}
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
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.1.1/gsap.min.js"></script>
    @stack('scripts')
</body>

</html>
