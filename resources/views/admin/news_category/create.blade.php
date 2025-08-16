@extends('adminlte::page')

@section('title','新增分類')

@section('content_header')
    <h1>新增分類</h1>
@stop

@section('content')
<form action="{{ route('admin.news_category.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-row">
        <div class="form-group col-md-4">
            <label>父類 (parent)</label>
            <select name="parent_id" class="form-control">
                <option value="">無</option>
                @foreach($parents as $p)
                    <option value="{{ $p->cat_id }}">ID {{ $p->cat_id }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>是否顯示</label>
            <select name="is_visible" class="form-control">
                <option value="1">顯示</option>
                <option value="0">隱藏</option>
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>排序</label>
            <input type="number" name="display_order" value="0" class="form-control">
        </div>
    </div>

    <hr>

    <div class="card">
        <div class="card-header">多語系（請為每個語系至少填寫名稱）</div>
        <div class="card-body">
            @foreach($langs as $lang)
                <h5>{{ $lang->name }} ({{ $lang->code }})</h5>

                <div class="form-group">
                    <label>名稱 (name)</label>
                    <input type="text" name="desc[{{ $lang->lang_id }}][name]" class="form-control" >
                </div>

                <div class="form-group">
                    <label>簡述 (description)</label>
                    <input type="text" name="desc[{{ $lang->lang_id }}][description]" class="form-control" >
                </div>

                <div class="form-group">
                    <label>內容 (content) - 使用 CKEditor</label>
                    <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control ckeditor" rows="6"></textarea>
                </div>

                <hr>
            @endforeach
        </div>
    </div>

    <div class="text-right mt-3">
        <a href="{{ route('admin.news_category.index') }}" class="btn btn-secondary">返回</a>
        <button class="btn btn-success">送出</button>
    </div>
</form>
@stop

@section('js')
    {{-- CKEditor CDN（簡單整合） --}}
    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
    <script>
        // 初始化所有 .ckeditor textarea
        document.querySelectorAll('.ckeditor').forEach(el => {
            CKEDITOR.replace(el);
        });
    </script>
@stop
