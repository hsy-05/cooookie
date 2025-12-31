@extends('adminlte::page')

@section('title', '消息分類管理')

@section('content_header')
    <h1>消息分類管理</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/backend.css') }}">
@stop

@section('content')
    {{-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 --}}
    <x-admin.page-message>
        <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
            {{-- <!-- 搜尋表單 -->
            <form action="{{ route('admin.news.index') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜尋標題..." value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit">搜尋</button>
                        <!-- 如果有搜尋關鍵字，顯示清除按鈕 -->
                        @if ($search)
                            <a href="{{ route('admin.news.index', request()->except('search', 'page')) }}"
                                class="btn btn-light">清除</a>
                        @endif
                    </div>
                </div>
                <!-- 保留其他查詢參數，例如 per_page -->
                @foreach (request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form> --}}

            <!-- 新增按鈕 -->
            <a href="{{ route('admin.news_category.create') }}" class="btn btn-primary mb-3 ml-auto">新增分類</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    {{-- <th>cat_id</th> --}}
                    <th class="text-center">名稱</th>
                    <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                    <th class="text-center px-width-150 hidden-md">排序</th>
                    <th class="text-center px-width-150 hidden-sm">更新時間</th>
                    <th class="text-center px-width-130 table-actions">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $cat)
                    <tr>
                        {{-- <td>{{ $cat->cat_id }}</td> --}}
                        <td class="text-center">
                            @foreach ($cat->descs as $d)
                                <div><strong>{{-- [{{ $d->lang_id }}]</strong>  --}}{{ $d->name }}</div>
                            @endforeach
                        </td>
                        {{-- 是否顯示 --}}
                        <td class="text-center hidden-xs">
                            <!-- AdminLTE Custom Switch Element -->
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                    id="newsSwitch{{ $cat->cat_id }}" data-id="{{ $cat->cat_id }}" data-model="NewsCategory"
                                    {{-- 指定模型名稱 --}} data-field="is_visible" {{-- 指定要更新的欄位 --}}
                                    {{ $cat->is_visible ? 'checked' : '' }}>
                                <label class="custom-control-label" for="newsSwitch{{ $cat->cat_id }}"></label>
                            </div>
                        </td>
                        <td class="text-center hidden-md">{{ $cat->display_order }}</td>
                        <td class="hidden-sm">{{ $cat->updated_at->format('Y-m-d H:i') }}</td>
                        <td>
                            {{-- <a href="{{ route('admin.news_category.show', $cat->cat_id) }}" class="btn btn-sm btn-info">查看</a> --}}
                            <a href="{{ route('admin.news_category.edit', $cat->cat_id) }}"
                                class="btn btn-sm btn-warning">編輯</a>
                            <form action="{{ route('admin.news_category.destroy', $cat->cat_id) }}" method="POST"
                                style="display:inline" onsubmit="return confirm('確定刪除？')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">刪除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">目前沒有任何記錄。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </x-admin.page-message>
@stop
