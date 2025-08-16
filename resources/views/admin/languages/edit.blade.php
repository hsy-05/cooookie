@extends('adminlte::page')
@section('title','編輯語系')
@section('content_header') <h1>編輯語系</h1> @stop
@section('content')
<form action="{{ route('admin.languages.update', $language->lang_id) }}" method="POST">
    @csrf @method('PUT')
    <div class="form-group"><label>語系名稱</label><input name="name" class="form-control" value="{{ $language->name }}" required></div>
    <div class="form-group"><label>別名</label><input name="alias" class="form-control" value="{{ $language->alias }}"></div>
    <div class="form-group"><label>代碼（code）</label><input name="code" class="form-control" value="{{ $language->code }}" required></div>
    <div class="form-group"><label>ISO code</label><input name="iso_code" class="form-control" value="{{ $language->iso_code }}"></div>
    <div class="form-group"><label>區域</label><input name="region" class="form-control" value="{{ $language->region }}"></div>
    <div class="form-group"><label>排序</label><input name="sort_order" type="number" class="form-control" value="{{ $language->sort_order }}"></div>
    <div class="form-group"><label>啟用</label>
        <select name="enabled" class="form-control">
            <option value="1" {{ $language->enabled ? 'selected' : '' }}>啟用</option>
            <option value="0" {{ !$language->enabled ? 'selected' : '' }}>停用</option>
        </select>
    </div>
    <div class="form-group"><label>顯示範圍</label>
        <select name="display_scope" class="form-control">
            <option value="both" {{ $language->display_scope=='both' ? 'selected' : '' }}>前後台</option>
            <option value="backend" {{ $language->display_scope=='backend' ? 'selected' : '' }}>僅後台</option>
        </select>
    </div>

    <div class="text-right">
        <a href="{{ route('admin.languages.index') }}" class="btn btn-secondary">返回</a>
        <button class="btn btn-success">更新</button>
    </div>
</form>
@stop
