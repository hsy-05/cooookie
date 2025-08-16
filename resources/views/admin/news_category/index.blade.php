@extends('adminlte::page')

@section('title', '消息分類管理')

@section('content_header')
    <h1>消息分類管理</h1>
@stop

@section('content')
    <div class="text-right">
        <a href="{{ route('admin.news_category.create') }}" class="btn btn-primary mb-3">新增分類</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>cat_id</th>
                <th>名稱 (各語系)</th>
                <th>是否顯示</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $c)
                <tr>
                    <td>{{ $c->cat_id }}</td>
                    <td>
                        @foreach ($c->descs as $d)
                            <div><strong>[{{ $d->lang_id }}]</strong> {{ $d->name }}</div>
                        @endforeach
                    </td>
                    <td>{{ $c->is_visible ? '是' : '否' }}</td>
                    <td>{{ $c->display_order }}</td>
                    <td>
                        <a href="{{ route('admin.news_category.show', $c->cat_id) }}" class="btn btn-sm btn-info">查看</a>
                        <a href="{{ route('admin.news_category.edit', $c->cat_id) }}" class="btn btn-sm btn-warning">編輯</a>
                        <form action="{{ route('admin.news_category.destroy', $c->cat_id) }}" method="POST"
                            style="display:inline" onsubmit="return confirm('確定刪除？')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">刪除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
