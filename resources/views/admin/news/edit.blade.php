@extends('adminlte::page')
@section('title','編輯消息')
@section('content_header') <h1>編輯消息</h1> @stop
@section('content')
<form action="{{ route('admin.news.update', $news->news_id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="form-row">
        <div class="col-md-4 form-group">
            <label>分類</label>
            <select name="cat_id" class="form-control">
                <option value="">-- 無 --</option>
                @foreach($cats as $cat)
                    <option value="{{ $cat->cat_id }}" {{ $cat->cat_id == $news->cat_id ? 'selected' : '' }}>
                        {{ $cat->descs->first()->name ?? 'ID-'.$cat->cat_id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 form-group">
            <label>是否顯示</label>
            <select name="is_visible" class="form-control">
                <option value="1" {{ $news->is_visible ? 'selected' : '' }}>顯示</option>
                <option value="0" {{ !$news->is_visible ? 'selected' : '' }}>隱藏</option>
            </select>
        </div>

        <div class="col-md-2 form-group">
            <label>排序</label><input type="number" name="display_order" class="form-control" value="{{ $news->display_order }}">
        </div>

        <div class="col-md-4 form-group">
            <label>目前封面</label><br>
            @if($news->image)<img src="{{ asset('storage/' . $news->image) }}" width="200">@endif
        </div>
    </div>

    <div class="form-group">
        <label>更換封面圖片</label>
        <input type="file" name="image" class="form-control">
    </div>

    <div class="card mt-3"><div class="card-header">多語系內容</div>
    <div class="card-body">
        @foreach($langs as $lang)
            @php $d = $descMap[$lang->lang_id] ?? null; @endphp
            <h5>{{ $lang->name }} ({{ $lang->code }})</h5>
            <div class="form-group"><label>標題</label><input type="text" name="desc[{{ $lang->lang_id }}][title]" class="form-control" value="{{ $d->title ?? '' }}"></div>
            <div class="form-group"><label>內容</label><textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control" rows="6">{{ $d->content ?? '' }}</textarea></div>
            <hr>
        @endforeach
    </div></div>

    <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary mr-2">返回</a>
        <button class="btn btn-success">更新</button>
    </div>
</form>
@stop
