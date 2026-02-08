@props(['tabs' => null, 'content', 'footer' => null])

{{--
    專業說明：
    1. 使用 $customCardClass 變數，這會由 Middleware 統一注入。
    2. 若變數不存在（防呆），則回退到預設的 card-primary。
--}}
<div class="card {{ $customCardClass ?? 'card-primary card-outline card-outline-tabs' }}">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs custom-styled-tabs" role="tablist">
            {{ $tabs }}
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content">
            {{ $content }}
        </div>
    </div>

    @if($footer)
        <div class="card-footer table-actions-container">
            {{ $footer }}
        </div>
    @endif
</div>
