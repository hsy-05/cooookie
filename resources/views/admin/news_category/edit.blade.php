@extends('adminlte::page')

@section('title','編輯分類')

@section('content_header')
    <h1>編輯分類</h1>
@stop

@section('content')
<form action="{{ route('admin.news_category.update', $news_category->cat_id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-row">
        <div class="form-group col-md-4">
            <label>父類 (parent)</label>
            <select name="parent_id" class="form-control">
                <option value="">無</option>
                @foreach($parents as $p)
                    <option value="{{ $p->cat_id }}" {{ $p->cat_id == $news_category->parent_id ? 'selected' : '' }}>
                        ID {{ $p->cat_id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>是否顯示</label>
            <select name="is_visible" class="form-control">
                <option value="1" {{ $news_category->is_visible ? 'selected' : '' }}>顯示</option>
                <option value="0" {{ !$news_category->is_visible ? 'selected' : '' }}>隱藏</option>
            </select>
        </div>

        <div class="form-group col-md-2">
            <label>排序</label>
            <input type="number" name="display_order" value="{{ $news_category->display_order }}" class="form-control">
        </div>
    </div>

    <hr>

    <div class="card">
        <div class="card-header">多語系</div>
        <div class="card-body">
            @foreach($langs as $lang)
                @php $d = $descMap[$lang->lang_id] ?? null; @endphp

                <h5>{{ $lang->name }} ({{ $lang->code }})</h5>

                <div class="form-group">
                    <label>名稱 (name)</label>
                    <input type="text" name="desc[{{ $lang->lang_id }}][name]" class="form-control" value="{{ $d->name ?? '' }}">
                </div>

                <div class="form-group">
                    <label>簡述 (description)</label>
                    <input type="text" name="desc[{{ $lang->lang_id }}][description]" class="form-control" value="{{ $d->description ?? '' }}">
                </div>

                <div class="form-group">
                    <label>內容 (content)</label>
                    <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control ckeditor" rows="6">{{ $d->content ?? '' }}</textarea>
                </div>

                <hr>
            @endforeach
        </div>
    </div>

    <div class="text-right mt-3">
        <a href="{{ route('admin.news_category.index') }}" class="btn btn-secondary">返回</a>
        <button class="btn btn-success">更新</button>
    </div>
</form>
@stop

@section('js')
    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
    <script>
        document.querySelectorAll('.ckeditor').forEach(el => {
            CKEDITOR.replace(el);
        });
    </script>
@stop
