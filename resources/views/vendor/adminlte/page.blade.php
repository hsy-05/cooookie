@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

{{--
    ⭐ 面試重點：伺服器端渲染 (SSR) 解決 FOUC 問題
    我們直接在 PHP 層級組合 Body Classes，這樣頁面一出來就是深色/淺色，
    使用者不會看到白色背景閃一下變黑的情況。
--}}
@php
    // 取得使用者設定 (防呆：若無設定則給空陣列)
    $userPrefs = auth()->user()->preferences ?? [];

    // --- 1. Body Classes 處理 ---
    $customBodyClasses = [];
    if (!empty($userPrefs['dark_mode'])) $customBodyClasses[] = 'dark-mode';
    if (!empty($userPrefs['sidebar_collapse'])) $customBodyClasses[] = 'sidebar-collapse';
    if (!empty($userPrefs['accent_color'])) $customBodyClasses[] = $userPrefs['accent_color'];

    $finalBodyClasses = $layoutHelper->makeBodyClasses() . ' ' . implode(' ', $customBodyClasses);

    // --- 2. 準備傳給子元件的參數 (用於 Navbar 和 Sidebar) ---
    // 這裡我們把設定存進變數，等等要在 @include 時傳進去
    $prefNavbarColor = $userPrefs['navbar_color'] ?? 'navbar-white navbar-light';
    $prefSidebarTheme = $userPrefs['sidebar_theme'] ?? 'sidebar-dark-primary';
    $prefNavFlat = !empty($userPrefs['nav_flat']) ? 'nav-flat' : '';
    $prefBrandColor = $userPrefs['brand_color'] ?? '';
@endphp

@section('classes_body', $finalBodyClasses)

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">
        {{-- Preloader Animation --}}
        @if ($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @include('adminlte::partials.navbar.navbar', ['prefNavbarColor' => $prefNavbarColor])

        {{-- Left Main Sidebar --}}
        @include('adminlte::partials.sidebar.left-sidebar', [
            'prefSidebarTheme' => $prefSidebarTheme,
            'prefNavFlat' => $prefNavFlat,
            'prefBrandColor' => $prefBrandColor
        ])

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var toggleBooleanUrl = "{{ route('admin.toggle.boolean') }}";
        var csrfToken = "{{ csrf_token() }}";
    </script>

    <script src="{{ asset('js/admin/common.js') }}"></script>

    {{-- 【關鍵修正】 --}}
    {{-- 1. 使用 window.UserPrefs 儲存偏好 --}}
    {{-- 2. 加上分號 ; 防止語法錯誤 --}}
    {{-- 3. 使用 {!! !!} 與 json_encode 確保輸出標準 JSON 格式 --}}
    <script>
        window.UserPrefs = {!! json_encode(auth()->user()->preferences ?? []) !!};
    </script>

    {{-- 引入自訂主題 JS (需放在 UserPrefs 宣告之後) --}}
    <script src="{{ asset('js/admin/theme.js') }}"></script>

    <script>
        // Session 訊息提示
        @if (session('form_success_swal'))
            showAlert('success', '成功', {!! json_encode(session('form_success_swal')) !!});
        @endif
        @if (session('form_error_swal'))
            showAlert('error', '錯誤', {!! json_encode(session('form_error_swal')) !!});
        @endif
    </script>
@stop
