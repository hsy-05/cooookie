@extends('adminlte::page')

@section('title','分類資料')

@section('content_header')
    <h1>分類資料</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>cat_id：</strong> {{ $category->cat_id }}</p>
        <p><strong>父類：</strong> {{ $category->parent_id }}</p>
        <p><strong>是否顯示：</strong> {{ $category->is_visible ? '是' : '否' }}</p>
        <p><strong>排序：</strong> {{ $category->display_order }}</p>
        <hr>
        <h4>多語系內容</h4>
        @foreach($category->descs as $d)
            <h5>[{{ $d->lang_id }}] {{ $d->name }}</h5>
            <p><strong>簡述：</strong> {{ $d->description }}</p>
            <div><strong>內容：</strong>{!! $d->content !!}</div>
            <hr>
        @endforeach
    </div>
</div>
@stop
