@extends('adminlte::page')

@section('title', $pageTitle)

@include('components.frontend.page_content_header')

@section('content')
    <x-frontend.page-message>
        <!-- 📄 Summernote 範本插入 Modal -->
        @include('components.summernote.template-modal')
        <form action="{{ isset($isEdit) ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($isEdit))
                @method('PUT')
            @endif

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
                            <ul class="nav nav-tabs  mb-3" id="language-tabs" role="tablist">
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
                                                value="{{ $d->title ?? '' }}" required>
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
                                            @if (isset($isEdit) && $news->image)
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" data-toggle="modal"
                                                        data-target="#imageModal">瀏覽</button>
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">點擊「瀏覽」查看已上傳的封面圖片</small>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="cat_id">分類</label>
                                        <select id="cat_id" name="cat_id" class="form-control">
                                            <option value="">-- 無 --</option>
                                            @foreach ($cats as $cat)
                                                <option value="{{ $cat->cat_id }}"
                                                    {{ isset($isEdit) && $cat->cat_id == $news->cat_id ? 'selected' : '' }}>
                                                    {{ optional($cat->descs->first())->name ?? 'ID-' . $cat->cat_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="is_visible">是否顯示</label>
                                        <select id="is_visible" name="is_visible" class="form-control">
                                            <option value="1"
                                                {{ isset($isEdit) && $news->is_visible ? 'selected' : '' }}>
                                                顯示</option>
                                            <option value="0"
                                                {{ isset($isEdit) && !$news->is_visible ? 'selected' : '' }}>隱藏
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="display_order">排序</label>
                                        <input type="number" id="display_order" name="display_order" class="form-control"
                                            @if (isset($isEdit)) value="{{ $news->display_order }}" @endif>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 消息內容頁籤 -->
                    <div class="tab-pane fade" id="content">
                        <!-- 語系內容的分頁 -->
                        <ul class="nav nav-tabs mt-2" role="tablist">
                            @foreach ($langs as $lang)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                        href="#content-{{ $lang->lang_id }}">{{ $lang->name }}
                                        ({{ $lang->code }})
                                    </a>
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
                <button type="submit" class="btn btn-success">{{ isset($isEdit) ? '更新' : '新增' }}</button>
            </div>
        </form>
    </x-frontend.page-message>

    <!-- 圖片預覽彈出視窗 -->
    @if (isset($isEdit))
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
                        <img src="{{ $UPLOAD_PATH . '/' . $news->image }}" class="img-fluid" alt="封面圖片">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@stop

@section('js')
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <!-- 引入自訂的 Summernote 初始化檔 -->
    <script src="{{ asset('js/admin/summernote-init.js') }}"></script>

    <script>
        // AJAX 預設帶 CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        {{-- 強制送出前同步 Summernote 內容 --}}
        $('form').on('submit', function() {
            $('.summernote').each(function() {
                // 將Summernote內容同步回 textarea
                const content = $(this).summernote('code');
                $(this).val(content);
            });

            // 防止重複送出
            $(this).find('button[type="submit"]').prop('disabled', true).text('處理中...');
        });
    </script>
@stop
