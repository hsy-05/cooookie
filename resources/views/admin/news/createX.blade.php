@extends('adminlte::page')

@section('title', '編輯消息')

@section('content_header')
    <h1>編輯消息</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- 表單頁籤 -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs" id="form-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab"
                        aria-controls="general" aria-selected="true">一般資料</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="content-tab" data-toggle="tab" href="#content" role="tab"
                        aria-controls="content" aria-selected="false">消息內容</a>
                </li>
            </ul>

            <div class="tab-content" id="form-tabs-content">
                <!-- 一般資料頁籤 -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <!-- 語系頁籤 -->
                    <div class="nav-tabs-custom mt-3">
                        <ul class="nav nav-tabs" id="language-tabs" role="tablist">
                            @foreach ($langs as $lang)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="lang-{{ $lang->lang_id }}-tab" data-toggle="tab"
                                        href="#lang-{{ $lang->lang_id }}" role="tab"
                                        aria-controls="lang-{{ $lang->lang_id }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->name }}
                                        ({{ $lang->code }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="language-tabs-content">
                            @foreach ($langs as $lang)
                                @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                    aria-labelledby="lang-{{ $lang->lang_id }}-tab">
                                    <div class="form-group">
                                        <label for="title_{{ $lang->lang_id }}">標題</label>
                                        <input type="text" id="title_{{ $lang->lang_id }}"
                                            name="desc[{{ $lang->lang_id }}][title]" class="form-control"
                                            value="{{ $d->title ?? '' }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 共同設定區 -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>共同設定</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="image">封面圖片</label>
                                    <div class="input-group">
                                        <input type="file" id="image" name="image" class="form-control"
                                            aria-label="Upload image">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#imageModal">瀏覽</button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">點擊「瀏覽」查看已上傳的封面圖片</small>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="cat_id">分類</label>
                                    <select id="cat_id" name="cat_id" class="form-control">
                                        <option value="">-- 無 --</option>
                                        @foreach ($cats as $cat)
                                            <option value="{{ $cat->cat_id }}">
                                                {{ $cat->descs->first()->name ?? 'ID-' . $cat->cat_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="is_visible">是否顯示</label>
                                    <select id="is_visible" name="is_visible" class="form-control">
                                        <option value="1">顯示</option>
                                        <option value="0">隱藏</option>
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label for="display_order">排序</label>
                                    <input type="number" id="display_order" name="display_order" class="form-control"
                                        value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 消息內容頁籤 --> <!-- 消息內容 -->
                <div class="tab-pane fade" id="content">
                    <!-- 語系內容的分頁 -->
                    <ul class="nav nav-tabs mt-2" role="tablist">
                        @foreach ($langs as $lang)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                    href="#content-{{ $lang->lang_id }}">{{ $lang->name }} ({{ $lang->code }})</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3">
                        @foreach ($langs as $lang)
                            @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="content-{{ $lang->lang_id }}">
                                <div class="form-group">
                                    <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote">{{ $d->content ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按鈕 -->
        <div class="text-right mt-3">
            <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">返回</a>
            <button type="submit" class="btn btn-success">更新</button>
        </div>
    </form>

    <!-- 圖片預覽彈出視窗 -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">封面圖片預覽</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="" class="img-fluid" alt="封面圖片">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <script>
        // ✅ Step 1：AJAX 預設帶 CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('.summernote').summernote({
                height: 300,
                lang: 'zh-TW',
                callbacks: {
                    onImageUpload: function(files) {
                        const editor = $(this); // 取得當前的 Summernote 物件
                        sendFile(files[0], editor);
                    }
                }
            });

            function sendFile(file, editor) {
                let data = new FormData();
                data.append("image", file);
                const uploadUrl = "{{ url('/admin/upload-image') }}";

                $.ajax({
                    url: uploadUrl,
                    method: 'POST',
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function(url) {
                        editor.summernote('insertImage', url); // 正確插入圖片
                    },
                    error: function(err) {
                        alert("上傳圖片失敗");
                    }
                });
            }
        });
    </script>
@stop
