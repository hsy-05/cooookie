<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', '網站') - 公司名稱</title>

    <link href="{{ asset('favicons/favicon.ico') }}" rel="icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
</head>

<body>

    {{-- Header --}}
    <header class="site-header">
        <div class="header-inner container d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
            </a>
            <nav class="site-nav d-flex align-items-center">
                <a href="{{ route('news.index') }}"
                    class="nav-link {{ request()->routeIs('news.index') ? 'active' : '' }}">最新消息</a>
                <a href="{{ url('/about') }}" class="nav-link">關於我們</a>
                <a href="{{ url('/products') }}" class="nav-link">產品</a>
                <a href="{{ url('/contact') }}" class="nav-link">聯絡我們</a>
            </nav>
        </div>
    </header>

    {{-- 主內容 --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- 頁尾 --}}
    <footer class="site-footer bg-light py-4 mt-5">
        <div class="container text-center text-muted small">
            © {{ date('Y') }} 公司名稱 - All rights reserved.
        </div>
    </footer>

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            var header = $('.site-header');
            var headerHeight = header.outerHeight();

            $(window).scroll(function() {
                if ($(this).scrollTop() > headerHeight) {
                    header.addClass('scrolled');
                } else {
                    header.removeClass('scrolled');
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
