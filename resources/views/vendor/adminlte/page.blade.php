@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if ($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if ($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if (!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

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

        {{-- Right Control Sidebar --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    {{-- 引入 SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var toggleBooleanUrl = "{{ route('admin.toggle.boolean') }}"; // 在全域變數中儲存路由
        var csrfToken = "{{ csrf_token() }}"; // 包含 CSRF token 以通過 Laravel 的 CSRF 保護
    </script>

    <!-- 在 <head> 或者 <body> 底部引入 common.js -->
    <script src="{{ asset('js/admin/common.js') }}"></script>
    <script>
        // 顯示成功提示 (來自 session)
        @if (session('form_success_swal'))
            showAlert('success', '成功', {!! json_encode(session('form_success_swal')) !!});
        @endif

        // 顯示錯誤提示 (來自 session)
        @if (session('form_error_swal'))
            showAlert('error', '錯誤', {!! json_encode(session('form_error_swal')) !!});
        @endif
    </script>
@stop
