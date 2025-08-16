@extends('adminlte::page')

@section('title','語言設定')

@section('content_header')
<h1>語言設定</h1>
@stop

@section('content')
<a href="{{ route('admin.languages.create') }}" class="btn btn-primary mb-2">新增語系</a>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th><th>名稱</th><th>代碼</th><th>排序</th><th>啟用</th><th>顯示範圍</th><th>操作</th>
        </tr>
    </thead>
    <tbody>
        @foreach($langs as $l)
        <tr>
            <td>{{ $l->lang_id }}</td>
            <td>{{ $l->name }} ({{ $l->alias }})</td>
            <td>{{ $l->code }}</td>
            <td>{{ $l->sort_order }}</td>
            <td>{{ $l->enabled ? '是' : '否' }}</td>
            <td>{{ $l->display_scope }}</td>
            <td>
                <a href="{{ route('admin.languages.edit', $l->lang_id) }}" class="btn btn-sm btn-warning">編輯</a>
                <form action="{{ route('admin.languages.destroy', $l->lang_id) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('確定刪除？若已有內容資料會被阻止刪除。')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">刪除</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@stop
