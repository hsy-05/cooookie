{{--
    File: resources/views/components/admin/page_content_header.blade.php
    Purpose:
    統一後台所有頁面的「頁首標題區」
    - 左：頁面主標題 + 目前動作（列表 / 新增 / 編輯）
    - 右：操作按鈕（由各頁自行透過 slot 傳入）

    設計理念：
    - 標題層級清楚，讓使用者一進頁面就知道「我在哪」
    - 不在各頁重複寫 header，方便後期統一調整樣式
--}}

@php
    // 直接取得 Controller 已經處理好的主標題 (例如：最新消息)
    $config = $titleConfig ?? ($GLOBALS['titleConfig'] ?? []);
    $mainDisplayTitle = $config['main'] ?? ($pageTitle ?? '未定義標題');

    // 取得動作名稱
    $actionName = class_basename(Route::currentRouteAction());
    $method = explode('@', $actionName)[1] ?? '';

    $labels = [
        'index' => ['text' => '列表', 'icon' => 'fa-list'],
        'create' => ['text' => '新增', 'icon' => 'fa-plus'],
        'edit' => ['text' => '編輯', 'icon' => 'fa-pen'],
        'show' => ['text' => '檢視', 'icon' => 'fa-eye'],
    ];

    $currentAction = $labels[$method] ?? ['text' => '操作', 'icon' => 'fa-info-circle'];
@endphp

@section('content_header')
    <header class="header-container container-fluid py-3">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="page-main-title m-0">
                    {{-- 這裡直接顯示主標題，不需要在 Blade 裡面進行字串拆解 --}}
                    <span class="title-text">{{ $mainDisplayTitle }}</span>

                    <i class="fas fa-angle-double-right divider-icon mx-2"></i>

                    <span class="title-text action-label">
                        <i class="fas {{ $currentAction['icon'] }} mr-1"></i>
                        {{ $currentAction['text'] }}
                    </span>
                </h1>
            </div>

            <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        </div>
    </header>
@stop
