@extends('adminlte::page')

@section('title', $pageTitle)

@include('components.admin.page_content_header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/backend.css') }}">
@stop

@section('content')
    {{-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 --}}
    <x-admin.page-message>
        <!-- 📄 Summernote 範本插入 Modal -->
        @include('components.summernote.template-modal')
        <form name="the-form" action="{{ $isEdit ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
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
                                    @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                        aria-labelledby="lang-{{ $lang->lang_id }}-tab">
                                        <div class="form-group">
                                            <label for="title_{{ $lang->lang_id }}">標題</label>
                                            <input type="text" id="title_{{ $lang->lang_id }}"
                                                name="desc[{{ $lang->lang_id }}][title]"
                                                class="form-control required-field" data-label="標題 ({{ $lang->name }})"
                                                value="{{ $desc->title ?? '' }}">
                                        </div>
                                        <!-- 簡述欄位 -->
                                        <div class="form-group">
                                            <label for="description_{{ $lang->lang_id }}">簡述</label>
                                            <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                maxlength="25" rows="3" placeholder="最多 25 個字">{{ $desc->description ?? '' }}</textarea>
                                        </div>

                                        {{-- 僅系統最高權限 (is_system = 1) 可見 --}}
                                        @if (auth()->user()->role->is_system)
                                            <div class="form-group p-3 mb-3 border border-danger rounded"
                                                style="background-color: #fff5f5;">
                                                <label class="text-danger font-weight-bold">
                                                    <i class="fas fa-user-secret"></i> 工程師/最高權限專用設定
                                                </label>
                                                <small class="form-text text-muted mb-2">此欄位僅 Super Admin
                                                    可見，用於設定系統內部參數。</small>

                                                <input type="text" name="system_code" class="form-control"
                                                    placeholder="輸入系統參數..." value="{{ $news->system_code ?? '' }}">
                                            </div>
                                        @endif
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
                                            @if ($isEdit && $news->image)
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" data-toggle="modal"
                                                        data-target="#imageModal">瀏覽</button>
                                                </div>
                                            @endif
                                        </div>
                                        @if (isset($imageSizes['image']))
                                            <small class="form-text text-muted">
                                                建議尺寸：{{ $imageSizes['image'][0] }} x {{ $imageSizes['image'][1] }}
                                            </small>
                                        @endif
                                    </div>


                                    <div class="col-md-6 form-group">
                                        <label for="cat_id">分類</label>
                                        <select id="cat_id" name="cat_id" class="form-control required-field">
                                            <option value="">-- 無 --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->cat_id }}"
                                                    {{ $isEdit && $cat->cat_id == $news->cat_id ? 'selected' : '' }}>
                                                    {{ optional($cat->descs->first())->name ?? 'ID-' . $cat->cat_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- 排序與是否顯示 -->
                                    <div class="row col-12">
                                        <div class="col-md-6 form-group">
                                            <label for="display_order">排序</label>
                                            <input type="number" id="display_order" name="display_order"
                                                class="form-control"
                                                @if ($isEdit) value="{{ $news->display_order }}" @endif>
                                        </div>
                                        <div class="col-md-2 form-group ml-3">
                                            <label for="is_visible">是否顯示</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_visible"
                                                    name="is_visible" value="1"
                                                    {{ !$isEdit || $news->is_visible ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_visible"></label>
                                            </div>
                                        </div>
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
                                @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="content-{{ $lang->lang_id }}">
                                    <div class="form-group">
                                        <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote {{-- required-field --}}"
                                            {{-- data-label="內容 ({{ $lang->name }})" --}}>{{ $desc->content ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 提交按鈕 -->
            <div class="text-center mt-3">
                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">返回</a>
                <button type="submit" class="btn btn-success">{{ $isEdit ? '更新' : '新增' }}</button>
            </div>
        </form>
    </x-admin.page-message>

    <!-- 圖片預覽彈出視窗 -->
    @if ($isEdit)
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

    <!-- Summernote 繁體中文語系 -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-zh-TW.min.js"></script>

    <!-- 引入自訂的 Summernote 初始化檔 -->
    <script src="{{ asset('js/admin/summernote-init.js') }}"></script>

    <script>
        $(function() {
            const BASE_URL = "{{ url('/') }}";
            console.log('BASE_URL defined:', BASE_URL); // 調試：檢查控制台
            const theForm = $('form[name="the-form"]');

            theForm.on('submit', function(e) {
                // 如果驗證失敗，阻止表單送出
                if (!validateRequiredFields(this)) {
                    e.preventDefault(); // 阻止表單提交
                    return false;
                }
                // 強制送出前同步 Summernote 內容
                syncSummernoteContentOnSubmit();
            });
        });
    </script>
@stop
