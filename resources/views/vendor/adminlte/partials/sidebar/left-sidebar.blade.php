<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-center">
    <div class="btn-group btn-group-sm w-100 px-2" role="group">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-info" title="後台首頁">
            <i class="fas fa-tachometer-alt"></i>
        </a>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-success" title="前台首頁">
            <i class="fas fa-home"></i>
        </a>
        <button type="button" id="btn-quick-clear" class="btn btn-warning" title="清除快取">
            <i class="fas fa-broom"></i>
        </button>
        <a href="/" class="btn btn-secondary" title="系統設定">
            <i class="fas fa-cogs"></i>
        </a>
    </div>
</div>
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

</aside>
