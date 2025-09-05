{{-- 全站前台 Layout（簡潔版） --}}
<!doctype html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', '網站') - 公司名稱</title>

    {{-- Bootstrap CDN（快速起手） --}}
    <link href="{{ asset('favicons/favicon.ico') }}" rel="icon">
    <link href="{{ asset('favicons/favicon.ico') }}" rel="shortcut icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}"> {{-- 自訂樣式 --}}
</head>

<body>
    <header class="site-header bg-white shadow-sm">
        <div class="container d-flex align-items-center py-3">
            <a href="./" class="navbar-brand">
                <img src="{{ asset('logo.png') }}" alt="Logo" style="height:44px;">
            </a>

            {{-- 主選單（可擴充） --}}
            <nav class="ml-auto">
                <a href="{{ route('news.index') }}" class="mx-2 text-dark">消息</a>
                {{-- <a href="{{ route('advert.category', 'idx_banner') }}" class="mx-2 text-dark">廣告</a> --}}
            </nav>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            {{-- 主要內容區塊，子頁會注入 --}}
            @yield('content')
        </div>
    </main>

    <footer class="site-footer bg-light py-4 mt-5">
        <div class="container text-center text-muted small">
            © {{ date('Y') }} 公司名稱 - All rights reserved.
        </div>
    </footer>

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
