@extends('adminlte::page')
@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    {{-- 顯示操作成功/失敗的 session 訊息 --}}
    <x-admin.page-message>

        @include('components.summernote.template-modal')

        {{--
            表單提交設定：
            1. 判斷 $isEdit 決定路由為 update 或 store
            2. enctype="multipart/form-data" 必加，否則圖片上傳會失敗
        --}}
        <form name="the-form" action="{{ $isEdit ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            {{-- RESTful 更新必備：Laravel 的偽造方法 --}}
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-12">
                <div class="card card-primary card-outline card-outline-tabs">

                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs custom-styled-tabs" id="custom-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#tab-content" role="tab">內容設定</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content" id="form-tabs-content">

                            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                                <div class="sub-language-wrapper p-3">

                                    <ul class="nav sub-language-tabs" role="tablist">
                                        @foreach ($langs as $lang)
                                            <li class="nav-item">
                                                {{-- 使用 lang-gen 前綴防止 ID 與內容層衝突 --}}
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

                                    <div class="tab-content mt-3">
                                        @foreach ($langs as $lang)
                                            @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                                aria-labelledby="lang-{{ $lang->lang_id }}-tab">
                                                <div class="form-group">
                                                    <label for="title_{{ $lang->lang_id }}">標題</label>
                                                    <input type="text" id="title_{{ $lang->lang_id }}"
                                                        name="desc[{{ $lang->lang_id }}][title]"
                                                        class="form-control required-field"
                                                        {{-- data-label="標題 ({{ $lang->name }})" --}}
                                                        value="{{ $desc->title ?? '' }}">
                                                </div>
                                                <!-- 簡述欄位 -->
                                                <div class="form-group">
                                                    <label for="description_{{ $lang->lang_id }}">簡述</label>
                                                    <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                        maxlength="25" rows="3" placeholder="最多 25 個字">{{ $desc->description ?? '' }}</textarea>
                                                </div>

                                                {{-- 🛡️ 開發者專用：僅 L1 Developer 可見，用於記錄系統 Debug 資訊 --}}
                                                @if (auth()->user()->isDeveloper())
                                                    <div class="form-group p-3 mb-3 border border-danger rounded">
                                                        <label class="text-danger"><i class="fas fa-code"></i>
                                                            開發者專用：偵錯備註</label>
                                                        <input type="text" name="dev_notes" class="form-control"
                                                            value="{{ $news->dev_notes }}">
                                                    </div>
                                                @endif

                                                {{-- 🛡️ 內部管理專用：L1 & L2 可見，調整排序權重 --}}
                                                @if (auth()->user()->isDeveloper() || auth()->user()->isInternalAdmin())
                                                    <div class="form-group border-info border p-3">
                                                        <label class="text-info"><i class="fas fa-user-shield"></i>
                                                            內部管理專用：權重排序</label>
                                                        <select name="internal_priority" class="form-control">
                                                            <option value="0"
                                                                {{ $news->internal_priority == 0 ? 'selected' : '' }}>一般
                                                            </option>
                                                            <option value="1"
                                                                {{ $news->internal_priority == 1 ? 'selected' : '' }}>優先
                                                            </option>
                                                            <option value="2"
                                                                {{ $news->internal_priority == 2 ? 'selected' : '' }}>最優先
                                                                (置頂)</option>
                                                        </select>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="card m-3 mt-0">
                                    <div class="card-header">
                                        <h5>共同設定</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="col-md-6 form-group">
                                                <label for="image">封面圖片</label>
                                                <div class="input-group">
                                                    <input type="file" id="image_url" name="image_url"
                                                        class="form-control" aria-label="Upload image">
                                                    @if ($isEdit && $news->image_url)
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-info" data-toggle="modal"
                                                                data-target="#imageModal">瀏覽</button>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if (isset($imageSizes['image_url']))
                                                    <small class="form-text text-muted">
                                                        建議尺寸：{{ $imageSizes['image_url'][0] }} x
                                                        {{ $imageSizes['image_url'][1] }}
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
                                            <div class="col-md-6 form-group">
                                                <label for="display_order">排序</label>
                                                <input type="number" name="display_order" class="form-control"
                                                    value="{{ $isEdit ? $news->display_order : 0 }}">
                                            </div>

                                            <div class="col-md-6 form-group">
                                                <label for="is_visible">是否顯示</label>
                                                <div class="custom-control custom-switch mt-2">
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

                            <!-- 消息內容頁籤 -->
                            <div class="tab-pane fade" id="tab-content" role="tabpanel">
                                <!-- 語系內容的分頁 -->
                                <div class="sub-language-wrapper p-3">

                                    <ul class="nav sub-language-tabs" role="tablist">
                                        @foreach ($langs as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                                    href="#lang-cnt-{{ $lang->lang_id }}" role="tab">
                                                    {{ $lang->name }}
                                                    <span class="small text-uppercase"
                                                        style="opacity: 0.6">({{ $lang->code }})</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="tab-content mt-3">
                                        @foreach ($langs as $lang)
                                            @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                                id="lang-cnt-{{ $lang->lang_id }}" role="tabpanel">
                                                <div class="form-group">
                                                    <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote"
                                                        {{-- data-label="內容 ({{ $lang->name }})" --}}>{{ $desc->content ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 提交按鈕 -->
                    <div class="card-footer table-actions-container">
                        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">返回</a>
                        <button type="submit" class="btn btn-success">{{ $isEdit ? '更新' : '新增' }}</button>
                    </div>
                </div>
            </div>
        </form>
    </x-admin.page-message>

    <!-- 圖片預覽彈出視窗 -->
    @if ($isEdit)
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">封面圖片預覽</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ $UPLOAD_PATH . '/' . $news->image_url }}" class="img-fluid" alt="封面圖片">
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
    {{-- Summernote 相關資源引入 --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

    <!-- Summernote 繁體中文語系 -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-zh-TW.min.js"></script>

    <!-- 引入自訂的 Summernote 初始化檔 -->
    <script src="{{ asset('js/admin/summernote-init.js') }}"></script>

    <script>
        $(function() {
            const BASE_URL = "{{ url('/') }}";
            const theForm = $('form[name="the-form"]');

            // 表單提交前的最終處理
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
