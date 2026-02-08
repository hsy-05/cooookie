{{--
    File: resources/views/components/admin/page_content_header.blade.php
    Purpose: 統一後台頁頭樣式，水平呈現標題與動作
--}}

@php
    // 1. 自動偵測目前的 Controller 方法
    $actionName = class_basename(Route::currentRouteAction());
    $method = explode('@', $actionName)[1] ?? '';

    // 2. 動作標籤參數化
    $labels = [
        'index'  => ['text' => '列表', 'icon' => 'fa-list'],
        'create' => ['text' => '新增', 'icon' => 'fa-plus'],
        'edit'   => ['text' => '編輯', 'icon' => 'fa-pen'],
        'show'   => ['text' => '檢視', 'icon' => 'fa-eye'],
    ];

    $currentAction = $labels[$method] ?? ['text' => '操作', 'icon' => 'fa-info-circle'];
@endphp

@section('content_header')
<div class="container-fluid header-container">
    <div class="row align-items-center"> {{-- 改用 center 讓大小字水平線對齊更完美 --}}
        <div class="col-12">
            <h1 class="page-main-title">
                <span class="title-text">{{ $pageTitle }}</span>
                <i class="fas fa-angle-double-right divider-icon"></i>
                <span class="title-text action-label">
                    <i class="fas {{ $currentAction['icon'] }}"></i>
                    {{ $currentAction['text'] }}
                </span>
            </h1>
        </div>
    </div>
</div>

@stop
