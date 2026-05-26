@extends('adminlte::page')

@section('title', $pageTitle)

@include('components.admin.page_content_header')

@section('content')
    {{-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 --}}
    <x-admin.page-message>
        <!-- 📄 Summernote 範本插入 Modal -->
        @include('components.admin.summernote.template-modal')

        <form
            action="{{ $isEdit ? route('admin.product_category.update', $category->cat_id) : route('admin.product_category.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

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
                                                <label for="name_{{ $lang->lang_id }}">標題</label>
                                                <input type="text" id="name_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][name]"
                                                    class="form-control required-field"
                                                    value="{{ $descMap[$lang->lang_id]->name ?? '' }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="description_{{ $lang->lang_id }}">標題</label>
                                                <input type="text" id="description_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][description]"
                                                    class="form-control required-field"
                                                    value="{{ $descMap[$lang->lang_id]->description ?? '' }}">
                                            </div>
                                            {{-- <div class="form-group">
                                                <label for="description_{{ $lang->lang_id }}">簡述</label>
                                                <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                    maxlength="25" rows="3" placeholder="最多 25 個字">{{ $descMap[$lang->lang_id]->description ?? '' }}</textarea>
                                            </div> --}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card m-3 mt-0">
                                <div class="card-header"><h5>共同設定</h5></div>
                                <div class="card-body">
                                    <div class="form-row">
                                        {{-- 圖片上傳區塊 --}}
                                        <div class="col-md-6 form-group">
                                            <label for="image_url">
                                                封面圖片
                                                {{-- 這裡動態抓取 Controller 設定的建議尺寸 --}}
                                                @if (isset($fileConfigs['image_url']))
                                                <i class="fas fa-question-circle text-muted"
                                                data-toggle="tooltip"
                                                title="建議尺寸：{{ $fileConfigs['image_url']['width'] }} x {{ $fileConfigs['image_url']['height'] }} px ，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>

                                            <div class="input-group">
                                                <input type="file" id="image_url" name="image_url"
                                                    class="form-control image-upload-input"
                                                    accept="image/*">

                                                {{-- 操作按鈕組：包含預覽與刪除 (AJAX 刪除功能需對應後端路由) --}}
                                                <div class="input-group-append {{ ($isEdit && $category->image_url) ? '' : 'd-none' }}"
                                                    id="image-action-group-image_url">
                                                    @if ($isEdit && $category->image_url)
                                                        <button type="button"
                                                                class="btn btn-info js-open-preview"
                                                                data-url="{{ asset('storage/' . $category->image_url) }}">
                                                            瀏覽
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-delete-image"
                                                                data-url="{{ route('admin.product_category.delete-image', $category->cat_id) }}"
                                                                data-field="image_url">
                                                            刪除
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- 檔案資訊顯示區 --}}
                                            <div id="stats-adv_img_url" class="mt-1 small text-secondary upload-stats"></div>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label for="parent_id">父類 (Parent)</label>
                                            <select id="parent_id" name="parent_id" class="form-control">
                                                <option value="0">無 (最頂層)</option>
                                                @foreach ($parentsList as $parent)
                                                    {{--
                                                        防呆邏輯：
                                                        1. 如果該項目的 can_be_parent 為 false，則加上 disabled 禁止選取。
                                                        2. 同時在名稱後面加註「(層級限制)」，對使用者更友善。
                                                    --}}
                                                    <option value="{{ $parent->cat_id }}"
                                                        {{ old('parent_id', $category->parent_id ?? '') == $parent->cat_id ? 'selected' : '' }}
                                                        {{ !$parent->can_be_parent ? 'disabled' : '' }}>
                                                        {{ $parent->name }}
                                                        {{ !$parent->can_be_parent ? '(已達層級上限)' : '' }} (ID:
                                                        {{ $parent->cat_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">註：網站設定消息分類最高為
                                                {{ config('site_settings.category_levels.product') }} 層。</small>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label for="is_visible">是否顯示</label>
                                            <select id="is_visible" name="is_visible" class="form-control">
                                                <option value="1"
                                                    {{ !$isEdit || $category->is_visible ? 'selected' : '' }}>顯示
                                                </option>
                                                <option value="0"
                                                    {{ $isEdit && !$category->is_visible ? 'selected' : '' }}>隱藏
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label for="display_order">排序</label>
                                            <input type="number" id="display_order" name="display_order"
                                                class="form-control" value="{{ $isEdit ? $category->display_order : 0 }}">
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
                    </x-slot:content>

                    {{-- 底部按鈕區 --}}
                    <x-slot:footer>
                        <a href="{{ route('admin.product_category.index') }}" class="btn btn-secondary">返回</a>
                        <button type="submit" class="btn btn-success js-submit-btn">{{ $isEdit ? '更新' : '新增' }}</button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>
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
        // AJAX 預設帶 CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 強制送出前同步 Summernote 內容
        $('form').on('submit', function() {
            $('.summernote').each(function() {
                const content = $(this).summernote('code');
                $(this).val(content);
            });
        });
    </script>
@stop
