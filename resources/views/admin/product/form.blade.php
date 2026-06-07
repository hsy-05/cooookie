@extends('adminlte::page')
@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        @include('components.admin.summernote.template-modal')

        <form name="the-form" action="{{ $isEdit ? route('admin.product.update', $item->product_id) : route('admin.product.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            {{-- 返回網址 --}}
            <input type="hidden" name="back_url" value="{{ $backUrl ?? route('admin.product.index') }}">
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
                                                    maxlength="250" rows="3" placeholder="最多 25 個字">{{ $descMap[$lang->lang_id]->description ?? '' }}</textarea>
                                            </div>

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

                                        {{-- ========================================== --}}
                                        {{-- 第一排：分類 與 價格                         --}}
                                        {{-- ========================================== --}}

                                        {{-- 分類下拉選單 --}}
                                        <div class="col-md-6 form-group">
                                            <label for="cat_id">分類</label>
                                            <select id="cat_id" name="cat_id" class="form-control required-field">
                                                <option value="">-- 無 --</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->cat_id }}"
                                                        {{ $isEdit && $cat->cat_id == $item->cat_id ? 'selected' : '' }}>
                                                        {{ optional($cat->descs->first())->name ?? 'ID-' . $cat->cat_id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 💰 商品價格輸入框 --}}
                                        <div class="col-md-6 form-group">
                                            <label for="price">商品價格 (元)</label>
                                            <input type="number" name="price" id="price" class="form-control required-field"
                                                min="0" placeholder="請輸入商品定價" data-label="商品價格"
                                                value="{{ $isEdit ? $item->price : 0 }}">
                                        </div>


                                        {{-- 🧱 強制斷行牆：確保圖片一定在第二排開始 --}}
                                        <div class="w-100"></div>


                                        {{-- ========================================== --}}
                                        {{-- 第二排：封面圖片 (保持 col-md-6 半寬精緻感)   --}}
                                        {{-- ========================================== --}}

                                        <div class="col-md-6 form-group">
                                            <label for="image_url">
                                                封面圖片
                                                @if (isset($fileConfigs['image_url']))
                                                    <i class="fas fa-question-circle text-muted" data-toggle="tooltip"
                                                        title="建議尺寸：{{ $fileConfigs['image_url']['width'] }} x {{ $fileConfigs['image_url']['height'] }} px
                                                            ，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>

                                            <div class="input-group">
                                                <input type="file" id="image_url" name="image_url"
                                                    class="form-control image-upload-input" accept="image/*">

                                                <div class="input-group-append {{ $isEdit && $item->image_url ? '' : 'd-none' }}"
                                                    id="image-action-group-image_url">
                                                    @if ($isEdit && $item->image_url)
                                                        <button type="button" class="btn btn-info js-open-preview"
                                                            data-url="{{ asset('storage/' . $item->image_url) }}">
                                                            瀏覽
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-delete-image"
                                                            data-url="{{ route('admin.product.delete-image', $item->product_id) }}"
                                                            data-field="image_url">
                                                            刪除
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>


                                        {{-- 🧱 強制斷行牆：確保第三排不會跑上來補圖片右邊的空位 --}}
                                        <div class="w-100"></div>


                                        {{-- ========================================== --}}
                                        {{-- 第三排：排序、是否顯示、首頁顯示              --}}
                                        {{-- ========================================== --}}

                                        {{-- 排序 --}}
                                        <div class="col-md-6 form-group">
                                            <label for="display_order">排序</label>
                                            <input type="number" name="display_order" class="form-control"
                                                id="display_order" value="{{ $isEdit ? $item->display_order : 0 }}">
                                        </div>

                                        {{-- 是否顯示 --}}
                                        <div class="col-md-3 form-group">
                                            <label for="is_visible">是否顯示</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="is_visible"
                                                    name="is_visible" value="1"
                                                    {{ !$isEdit || $item->is_visible ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_visible"></label>
                                            </div>
                                        </div>

                                        {{-- 首頁顯示 --}}
                                        <div class="col-md-3 form-group">
                                            <label for="is_visible_home">首頁顯示</label>
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="is_visible_home"
                                                    name="is_visible_home" value="1"
                                                    {{ !$isEdit || $item->is_visible_home ? 'checked' : '' }}>
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
                        <a href="{{ $backUrl ?? route('admin.product.index') }}" class="btn btn-secondary">返回</a>
                        <button type="submit" class="btn btn-success js-submit-btn">{{ $isEdit ? '更新' : '新增' }}</button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>

@stop

{{-- 這裡放第三方套件的 CDN 資源 --}}
@push('js')
    {{-- 這裡我們依然建議用 @include 抽離，保持頁面乾淨 --}}
    @include('components.admin.summernote._summernote')
@endpush

@section('js')
    <script>
        $(function() {
            // 表單送出前的最後檢查與資料同步
            $('form[name="the-form"]').on('submit', function(e) {
                // 1. 驗證必填 (common.js)
                if (typeof validateRequiredFields === "function" && !validateRequiredFields(this)) {
                    e.preventDefault();
                    return false;
                }

                // 2. 同步內容 (common.js)
                if (typeof syncSummernoteContent === "function") {
                    syncSummernoteContent('form[name="the-form"]');
                }
            });
        });
    </script>
@stop
