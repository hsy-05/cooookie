@extends('adminlte::page')

@section('title', '廣告分類管理')

@section('content_header')
    <h1>廣告分類管理</h1>
@stop

@section('content')
    <div class="text-right mb-3">
        <a href="{{ route('admin.advert_category.create') }}" class="btn btn-primary">新增分類</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="text-center">ID</th>
                <th class="text-center">分類代碼</th>
                <th class="text-center">顯示名稱(預設語)</th>
                <th class="text-center">啟用欄位</th>
                <th class="text-center">排序</th>
                <th class="text-center">是否顯示</th>
                <th class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $row)
                <tr>
                    <td class="text-center">{{ $row->cat_id }}</td>
                    <td class="text-center">{{ $row->cat_code }}</td>
                    <td class="text-center">{{ $row->name() ?? '-' }}</td>
                    <td class="text-center">
                        @if (is_array($row->cat_func_scope))
                            <code>{{ implode(', ', $row->cat_func_scope) }}</code>
                        @else
                        <code>-</code>
                        @endif
                    </td>
                    <td class="text-center">{{ $row->display_order }}</td>
                    <td class="text-center">
                        @if ($row->is_visible)
                            <span class="badge badge-success">顯示</span>
                        @else
                            <span class="badge badge-secondary">隱藏</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a class="btn btn-sm btn-warning"
                            href="{{ route('admin.advert_category.edit', $row->cat_id) }}">編輯</a>
                        <form action="{{ route('admin.advert_category.destroy', $row->cat_id) }}" method="POST"
                            style="display:inline-block;" onsubmit="return confirm('確定要刪除嗎？')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">刪除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        {{ $list->links('pagination::bootstrap-4') }}
    </div>
@stop
