@php
    // 取得當前的 controller@method，例如 NewsController@edit
    $action = class_basename(Route::currentRouteAction());
    $method = explode('@', $action)[1] ?? '';

    $actionlbl = match ($method) {
        'index'  => '列表',
        'create' => '新增',
        'edit'   => '編輯',
        default  => '操作'
    };
@endphp

@section('content_header')
    <h1>{{ PAGE_TITLE }}
        <small class="hidden-480">
            <i class="ace-icon fa fa-angle-double-right"></i>
            {{ $actionlbl }}
        </small>
    </h1>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}">首頁</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('admin.news.index') }}">消息管理</a>
        </li>
        <li class="breadcrumb-item active">
            {{ PAGE_TITLE }}
        </li>
    </ol>
@stop
