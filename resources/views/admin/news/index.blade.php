@extends('adminlte::page')

{{-- 1. 瀏覽器分頁標題：依然使用完整標題，方便搜尋與辨識 --}}
@section('title', $pageTitle)

{{-- 2. 頁面內容區標題：使用組件並傳入預先處理好的標題物件 --}}
@component('components.admin.page_content_header', [
    'pageTitle' => $pageTitle, // 傳入字串（供組件內的備援邏輯使用）
    'titleConfig' => $titleConfig, // [關鍵] 傳入物件，讓組件直接抓 $titleConfig['main']
])
    @slot('actions')
        {{-- 權限判斷：確保有權限的人才看得到按鈕 --}}
        @if (auth()->user()->canDo($permissionName . '.create'))
            <a href="{{ route('admin.' . $permissionName . '.create') }}" class="btn btn-primary shadow-sm px-4">
                <i class="fas fa-plus mr-1"></i>
                新增
            </a>
        @endif
    @endslot
@endcomponent

@section('content')
    {{-- 系統訊息顯示（成功 / 錯誤） --}}
    <x-admin.page-message>

        <div class="card">
            <div class="card-body">

                {{-- ===== 1. 搜尋 + 新增 ===== --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
                    {{-- 搜尋表單 --}}
                    <form action="{{ route('admin.news.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="搜尋標題..."
                                value="{{ $search ?? '' }}">

                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">搜尋</button>

                                {{-- 有搜尋條件才顯示清除 --}}
                                @if ($search)
                                    <a href="{{ route('admin.news.index', request()->except('search', 'page')) }}"
                                        class="btn btn-light">
                                        清除
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- 保留其他 GET 參數 --}}
                        @foreach (request()->except('search', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>

                </div>

                {{-- ===== 2. 批次刪除表單 ===== --}}
                <form action="{{ route('admin.news.batch_destroy') }}" method="POST" id="batchDeleteForm">
                    @csrf
                    @method('DELETE')

                    {{-- ===== 3. 資料列表 ===== --}}
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                {{-- 全選 --}}
                                <th class="text-center px-width-50">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th class="text-center">標題</th>
                                <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                                <th class="text-center px-width-150 hidden-xs">首頁顯示</th>
                                <th class="text-center px-width-150 hidden-md">排序</th>
                                <th class="text-center px-width-150 hidden-sm">更新時間</th>
                                <th class="text-center px-width-120">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($newsList as $item)
                                <tr>
                                    {{-- 勾選框 --}}
                                    <td class="text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $item->news_id }}"
                                            class="row-checkbox">
                                    </td>

                                    {{-- 標題（多語系取第一筆） --}}
                                    <td>{{ $item->descs->first()->title ?? '--' }}</td>

                                    {{-- 是否顯示 --}}
                                    <td class="text-center hidden-xs">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                                id="is_visible{{ $item->news_id }}" data-id="{{ $item->news_id }}"
                                                data-model="News" data-field="is_visible"
                                                {{ $item->is_visible ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="is_visible{{ $item->news_id }}"></label>
                                        </div>
                                    </td>

                                    {{-- 首頁顯示 --}}
                                    <td class="text-center hidden-xs">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                                id="is_visible_home{{ $item->news_id }}" data-id="{{ $item->news_id }}"
                                                data-model="News" data-field="is_visible_home"
                                                {{ $item->is_visible_home ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="is_visible_home{{ $item->news_id }}"></label>
                                        </div>
                                    </td>

                                    {{-- 排序 --}}
                                    <td class="hidden-md text-center">
                                        {{ $item->display_order }}
                                    </td>

                                    {{-- 更新時間 --}}
                                    <td class="hidden-sm">
                                        {{ $item->updated_at->format('Y-m-d H:i') }}
                                    </td>

                                    {{-- 操作 --}}
                                    <td>
                                        <div class="table-actions-container">

                                            {{-- 編輯 --}}
                                            @can('news.edit')
                                                <a href="{{ route('admin.news.edit', $item->news_id) }}"
                                                    class="btn btn-sm btn-warning" title="編輯">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- 單筆刪除 --}}
                                            @can('news.delete')
                                                <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST"
                                                    id="deleteForm{{ $item->news_id }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                        data-id="{{ $item->news_id }}" title="刪除">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        目前沒有任何記錄
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>


                    {{-- 批次刪除工具列 --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                        <div class="form-inline">
                            <label class="mr-2 text-danger">
                                <i class="fas fa-trash-alt"></i> 批次刪除
                            </label>

                            <button type="button" class="btn btn-danger" id="batchDeleteBtn" disabled>
                                執行刪除
                            </button>
                        </div>

                        <div class="text-muted small">
                            * 請勾選要刪除的消息
                        </div>
                    </div>

                </form>

                {{-- ===== 分頁與工具區塊 ===== --}}
                @include('components.admin.pagination_tools', ['items' => $newsList])
            </div>
        </div>
    </x-admin.page-message>
@stop
