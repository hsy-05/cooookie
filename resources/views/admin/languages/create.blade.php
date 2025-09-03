@extends('adminlte::page')
@section('title','新增語系')
@section('content_header') <h1>新增語系</h1> @stop
@section('content')
<form action="{{ route('admin.languages.store') }}" method="POST">
    @csrf
    <div class="form-group"><label>語系名稱</label><input name="name" class="form-control" required></div>
    <div class="form-group"><label>別名</label><input name="alias" class="form-control"></div>
    <div class="form-group"><label>代碼（code）</label><input name="code" class="form-control" required></div>
    <div class="form-group"><label>ISO code</label><input name="iso_code" class="form-control"></div>
    <div class="form-group"><label>區域</label><input name="region" class="form-control"></div>
    <div class="form-group"><label>排序</label><input name="display_order" type="number" class="form-control" value="0"></div>
    <div class="form-group"><label>啟用</label>
        <select name="enabled" class="form-control">
            <option value="1">啟用</option><option value="0">停用</option>
        </select>
    </div>
    <div class="form-group"><label>顯示範圍</label>
        <select name="display_scope" class="form-control">
            <option value="both">前後台</option>
            <option value="backend">僅後台</option>
        </select>
    </div>

    <div class="text-right">
        <a href="{{ route('admin.languages.index') }}" class="btn btn-secondary">返回</a>
        <button class="btn btn-success">送出</button>
    </div>
</form>
@stop
