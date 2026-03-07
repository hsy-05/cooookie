<div class="user-panel mt-3 pb-3 mb-3 d-flex justify-content-center">
    <div class="btn-group btn-group-sm w-100 px-2" role="group">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-info" title="後台首頁">
            <i class="fas fa-tachometer-alt"></i>
        </a>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-success" title="前台首頁">
            <i class="fas fa-home"></i>
        </a>
        <button type="button" id="btn-quick-clear" class="btn btn-warning" title="清除快取"
            data-url="{{ route('admin.clear.cache') }}">
            <i class="fas fa-broom"></i>
        </button>
        <a href="{{ route('admin.system_settings.index') }}" class="btn btn-secondary" title="系統設定">
            <i class="fas fa-cogs"></i>
        </a>
    </div>
</div>
