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

        {{-- Footer Section --}}
    @section('footer')
        <div class="footer-content">
            <p>© 2023 My Custom Footer. All rights reserved.</p>
        </div>
    @endsection

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

        {{-- 圖片預覽 Modal --}}
        @include('components.admin.image-preview-modal')
    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    <script>
        var toggleBooleanUrl = "{{ route('admin.toggle.boolean') }}";
        var csrfToken = "{{ csrf_token() }}";
    </script>

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
