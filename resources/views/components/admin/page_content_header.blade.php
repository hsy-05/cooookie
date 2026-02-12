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
    /**
     * 取得目前 Controller 的 method 名稱
     * 例如：AdminController@index / create / edit
     * 用來判斷現在是「列表 / 新增 / 編輯」狀態
     */
    $actionName = class_basename(Route::currentRouteAction());
    $method = explode('@', $actionName)[1] ?? '';

    /**
     * 動作標籤設定表
     * 之後如果有新動作，只要加在這裡即可
     */
    $labels = [
        'index'  => ['text' => '列表', 'icon' => 'fa-list', 'color' => ''],
        'create' => ['text' => '新增', 'icon' => 'fa-plus', 'color' => ''],
        'edit'   => ['text' => '編輯', 'icon' => 'fa-pen', 'color' => ''],
        'show'   => ['text' => '檢視', 'icon' => 'fa-eye', 'color' => ''],
    ];

    // 若找不到對應 method，使用預設顯示
    $currentAction = $labels[$method] ?? [
        'text' => '操作',
        'icon' => 'fa-info-circle',
        'color' => ''
    ];
@endphp

@section('content_header')
<header class="header-container container-fluid py-3">
    <div class="row align-items-center">

        {{-- 左側：頁面標題 + 動作狀態 --}}
        <div class="col-sm-6">
            <h1 class="page-main-title m-0">
                <span class="title-text">{{ $pageTitle }}</span>

                <i class="fas fa-angle-double-right divider-icon mx-2"></i>

                <span class="title-text action-label {{ $currentAction['color'] }}">
                    <i class="fas {{ $currentAction['icon'] }} mr-1"></i>
                    {{ $currentAction['text'] }}
                </span>
            </h1>
        </div>

        {{-- 右側：操作按鈕（由各頁決定是否顯示） --}}
        <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
            @isset($actions)
                {{ $actions }}
            @endisset
        </div>

    </div>
</header>
@stop
