@extends('adminlte::page')
@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        @include('components.summernote.template-modal')

        <form name="the-form" action="{{ $isEdit ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            {{-- 返回網址 --}}
            <input type="hidden" name="back_url" value="{{ $backUrl ?? route('admin.news.index') }}">
            {{-- Summernote 圖片用 --}}
            <input type="hidden" name="editor_id" value="{{ time() }}">

            <div class="col-md-12">
                <x-admin.card-tabs>
                    {{-- 頁籤標題區 --}}
                    <x-slot:tabs>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-content" role="tab">內容設定</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-seo" role="tab">SEO設定</a>
                        </li>
                    </x-slot:tabs>

                    {{-- 頁籤內容區 --}}
                    <x-slot:content>
                        {{-- 一般資料頁籤 --}}
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                            {{-- 語言切換內容 --}}
                            <div class="sub-language-wrapper p-3">
                                <ul class="nav sub-language-tabs" role="tablist">
                                    @foreach ($langs as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="lang-{{ $lang->lang_id }}-tab" data-toggle="tab"
                                                href="#lang-{{ $lang->lang_id }}" role="tab">
                                                {{ $lang->name }} ({{ $lang->code }})
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content mt-3">
                                    @foreach ($langs as $lang)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                            id="lang-{{ $lang->lang_id }}" role="tabpanel">
                                            <div class="form-group">
                                                <label for="title_{{ $lang->lang_id }}">標題</label>
                                                <input type="text" id="title_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][title]"
                                                    class="form-control required-field"
                                                    @if($langs->count() > 1) data-label="標題 ({{ $lang->name }})" @endif
                                                    value="{{ $descMap[$lang->lang_id]->title ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="description_{{ $lang->lang_id }}">簡述</label>
                                                <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                    maxlength="25" rows="3" placeholder="最多 25 個字">{{ $descMap[$lang->lang_id]->description ?? '' }}</textarea>
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
                                                            (置頂)
                                                        </option>
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
                                        {{-- 圖片上傳區塊 --}}
                                        <div class="col-md-6 form-group">
                                            <label for="image_url">
                                                封面圖片
                                                {{-- 這裡動態抓取 Controller 設定的建議尺寸 --}}
                                                @if (isset($fileConfigs['image_url']))
                                                    <i class="fas fa-question-circle text-muted" data-toggle="tooltip"
                                                        title="建議尺寸：{{ $fileConfigs['image_url']['width'] }} x {{ $fileConfigs['image_url']['height'] }} px
                                                            ，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>

                                            <div class="input-group">
                                                <input type="file" id="image_url" name="image_url"
                                                    class="form-control image-upload-input" accept="image/*">

                                                {{-- 操作按鈕組：包含預覽與刪除 (AJAX 刪除功能需對應後端路由) --}}
                                                <div class="input-group-append {{ $isEdit && $news->image_url ? '' : 'd-none' }}"
                                                    id="image-action-group-image_url">
                                                    @if ($isEdit && $news->image_url)
                                                        <button type="button" class="btn btn-info js-open-preview"
                                                            data-url="{{ asset('storage/' . $news->image_url) }}">
                                                            瀏覽
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-delete-image"
                                                            data-url="{{ route('admin.news.delete-image', $news->news_id) }}"
                                                            data-field="image_url">
                                                            刪除
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 分類下拉選單 --}}
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
                                                id="display_order" value="{{ $isEdit ? $news->display_order : 0 }}">
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

                                        <div class="col-md-6 form-group">
                                            <label for="is_visible_home">首頁顯示</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="is_visible_home"
                                                    name="is_visible_home" value="1"
                                                    {{ !$isEdit || $news->is_visible_home ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_visible_home"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- 內容頁籤 --}}
                        <div class="tab-pane fade" id="tab-content" role="tabpanel">
                            {{-- 語言切換內容 --}}
                            <div class="sub-language-wrapper p-3">
                                <ul class="nav sub-language-tabs" role="tablist">
                                    @foreach ($langs as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                                href="#lang-cnt-{{ $lang->lang_id }}" role="tab">
                                                {{ $lang->name }} ({{ $lang->code }})
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content mt-3">
                                    @foreach ($langs as $lang)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                            id="lang-cnt-{{ $lang->lang_id }}" role="tabpanel">
                                            <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote">{{ $descMap[$lang->lang_id]->content ?? '' }}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                        {{-- SEO頁籤 --}}
                        <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                            {{-- 語言切換內容 --}}
                            <div class="sub-language-wrapper p-3">
                                <ul class="nav sub-language-tabs" role="tablist">
                                    @foreach ($langs as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                id="lang-{{ $lang->lang_id }}-tab" data-toggle="tab"
                                                href="#lang-{{ $lang->lang_id }}" role="tab">
                                                {{ $lang->name }} ({{ $lang->code }})
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content mt-3">
                                    @foreach ($langs as $lang)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                            id="lang-{{ $lang->lang_id }}" role="tabpanel">
                                            <div class="form-group">
                                                <label for="seo_h1_{{ $lang->lang_id }}">網頁 H1</label>
                                                <input type="text" id="seo_h1_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][seo_h1]" class="form-control"
                                                    value="{{ $descMap[$lang->lang_id]->seo_h1 ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="meta_title_{{ $lang->lang_id }}">網頁標題</label>
                                                <input type="text" id="meta_title_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][meta_title]" class="form-control"
                                                    value="{{ $descMap[$lang->lang_id]->meta_title ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <x-admin.tag-input label="網頁關鍵字 (SEO Keywords)"
                                                    name="desc[{{ $lang->lang_id }}][meta_keyword][]" :value="$descMap[$lang->lang_id]->meta_keyword ?? []" />
                                            </div>
                                            <div class="form-group">
                                                <label for="meta_description_{{ $lang->lang_id }}">網頁簡短描述</label>
                                                <input type="text" id="meta_description_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][meta_description]"
                                                    class="form-control"
                                                    value="{{ $descMap[$lang->lang_id]->meta_description ?? '' }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </x-slot:content>

                    {{-- 底部按鈕區 --}}
                    <x-slot:footer>
                        <a href="{{ $backUrl ?? route('admin.news.index') }}" class="btn btn-secondary">返回</a>
                        <button type="submit" class="btn btn-success js-submit-btn">{{ $isEdit ? '更新' : '新增' }}</button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>

@stop

@section('js')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-zh-TW.min.js"></script>
    <script src="{{ asset('js/admin/summernote-init.js') }}"></script>

    <script>
        $(function() {
            // 初始化 Tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // 表單送出前的驗證
            $('form[name="the-form"]').on('submit', function(e) {
                if (typeof validateRequiredFields === "function" && !validateRequiredFields(this)) {
                    e.preventDefault();
                    return false;
                }
                if (typeof syncSummernoteContentOnSubmit === "function") {
                    syncSummernoteContentOnSubmit();
                }
            });
        });
    </script>
@stop
