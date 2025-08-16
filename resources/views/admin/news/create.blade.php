@extends('adminlte::page')
@section('title', '新增消息')
@section('content_header') <h1>新增消息</h1> @stop
@section('content')
    <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-row">
            <div class="col-md-4 form-group">
                <label>分類</label>
                <select name="cat_id" class="form-control" required>
                    <option value="">請選擇分類</option>
                    @foreach ($cats as $cat)
                        <option value="{{ $cat->cat_id }}" {{ old('cat_id') == $cat->cat_id ? 'selected' : '' }}>
                            {{ optional($cat->descs->first())->name ?? '分類#' . $cat->cat_id }}
                        </option>
                    @endforeach
                </select>
                @error('cat_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2 form-group">
                <label>是否顯示</label>
                <select name="is_visible" class="form-control">
                    <option value="1">顯示</option>
                    <option value="0">隱藏</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>排序</label><input type="number" name="display_order" class="form-control" value="0">
            </div>
            <div class="col-md-4 form-group">
                <label>封面圖片 (600x400 建議)</label>
                <input type="file" name="image" class="form-control">
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">多語系內容（請輸入每個語系的標題/內容）</div>
            <div class="card-body">
                @foreach ($langs as $lang)
                    <h5>{{ $lang->name }} ({{ $lang->code }})</h5>
                    <div class="form-group">
                        <label>標題</label>
                        <input type="text" name="desc[{{ $lang->lang_id }}][title]" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>內容</label>
                        <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control" rows="6"></textarea>
                    </div>
                    <hr>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary mr-2">返回</a>
            <button class="btn btn-success">送出</button>
        </div>
    </form>
@stop
