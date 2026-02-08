{{-- resources/views/admin/advert/form.blade.php --}}
@extends('adminlte::page')

@section('title', $pageTitle)

{{-- 麵包屑與標題組件：統一由外部傳入變數控制 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        {{-- 表單提交路徑：透過 $isEdit 判斷 --}}
        <form id="advertForm" name="the-form"
            action="{{ $isEdit ? route('admin.advert.update', $advert->adv_id) : route('admin.advert.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-12">
                <x-admin.card-tabs>
                    {{-- 1. 頁籤定義區 --}}
                    <x-slot:tabs>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-preview" role="tab">其他／預覽</a>
                        </li>
                    </x-slot:tabs>

                    {{-- 2. 頁籤內容區 --}}
                    <x-slot:content>
                        {{-- 一般資料：含多語系與共同設定 --}}
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

                            {{-- A. 多語系標題區 --}}
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
                                                <label for="adv_name_{{ $lang->lang_id }}">廣告標題 ({{ $lang->code }})</label>
                                                <input type="text"
                                                       id="adv_name_{{ $lang->lang_id }}"
                                                       name="desc[{{ $lang->lang_id }}][adv_name]"
                                                       class="form-control required-field"
                                                       placeholder="請輸入標題"
                                                       value="{{ old('desc.' . $lang->lang_id . '.adv_name', $descMap[$lang->lang_id]->adv_name ?? '') }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- B. 共同設定區 (廣告核心邏輯) --}}
                            <div class="card m-3 mt-0 shadow-none border">
                                <div class="card-header bg-light"><h5>共同設定</h5></div>
                                <div class="card-body">

                                    {{-- 分類選擇：驅動下方欄位顯示/隱藏的關鍵 --}}
                                    <div class="form-row mb-3">
                                        <div class="col-md-6 form-group">
                                            <label for="cat_id">廣告分類</label>
                                            <select name="cat_id" id="cat_id" class="form-control select2 required-field">
                                                <option value="">-- 請選擇分類 --</option>
                                                @foreach ($cats as $cat)
                                                    <option value="{{ $cat->cat_id }}"
                                                        {{-- 將後端邏輯透過 JSON 傳給前端，不混雜在 HTML 屬性中 --}}
                                                        data-func='@json($cat->cat_func_scope)'
                                                        data-params='@json($cat->cat_params)'
                                                        {{ (old('cat_id', $advert->cat_id ?? '') == $cat->cat_id) ? 'selected' : '' }}>
                                                        [{{ $cat->cat_code }}] {{ optional($cat->descs->first())->cat_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- 圖片上傳區塊 --}}
                                    <div class="form-row">

                                        {{-- 1. 廣告圖 - 電腦版 --}}
                                        <div class="col-md-6 form-group field field-adv_img_url d-none">
                                            <label for="adv_img_url">
                                                廣告圖 (電腦版)
                                                {{-- 這裡動態抓取 Controller 設定的建議尺寸 --}}
                                                @if (isset($fileConfigs['adv_img_url']))
                                                <i class="fas fa-question-circle text-muted"
                                                data-toggle="tooltip"
                                                title="建議尺寸：{{ $fileConfigs['adv_img_url']['width'] }} x {{ $fileConfigs['adv_img_url']['height'] }} px
                                                        ，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>
                                            <div class="input-group">
                                                <input type="file" id="adv_img_url" name="adv_img_url"
                                                    class="form-control image-upload-input"
                                                    accept="image/*">

                                                {{-- 操作按鈕組：包含預覽與刪除 (AJAX 刪除功能需對應後端路由) --}}
                                                <div class="input-group-append {{ ($isEdit && $advert->adv_img_url) ? '' : 'd-none' }}"
                                                    id="image-action-group-adv_img_url">
                                                    @if ($isEdit && $advert->adv_img_url)
                                                        <button type="button"
                                                                class="btn btn-info js-open-preview"
                                                                data-url="{{ $UPLOAD_PATH . '/' . $advert->adv_img_url }}">
                                                            瀏覽
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-delete-image"
                                                                data-url="{{ route('admin.advert.delete-image', $advert->adv_id) }}"
                                                                data-field="adv_img_url">
                                                            刪除
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- 檔案資訊顯示區 --}}
                                            <div id="stats-adv_img_url" class="mt-1 small text-secondary upload-stats"></div>
                                        </div>

                                        {{-- 2. 廣告圖 - 手機版 --}}
                                        <div class="col-md-6 form-group field field-adv_img_m_url d-none">
                                            <label for="adv_img_m_url">
                                                廣告圖 (手機版)
                                                {{-- 這裡動態抓取 Controller 設定的建議尺寸 --}}
                                                @if (isset($fileConfigs['adv_img_url']))
                                                <i class="fas fa-question-circle text-muted"
                                                data-toggle="tooltip"
                                                title="建議尺寸：{{ $fileConfigs['adv_img_url']['width'] }} x {{ $fileConfigs['adv_img_url']['height'] }} px
                                                        ，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>

                                            <div class="input-group">
                                                <input type="file" id="adv_img_m_url" name="adv_img_m_url"
                                                    class="form-control image-upload-input"
                                                    accept="image/*">

                                                <div class="input-group-append {{ ($isEdit && $advert->adv_img_m_url) ? '' : 'd-none' }}"
                                                    id="image-action-group-adv_img_m_url">
                                                    @if ($isEdit && $advert->adv_img_m_url)
                                                        <button type="button"
                                                                class="btn btn-info js-open-preview"
                                                                data-url="{{ $UPLOAD_PATH . '/' . $advert->adv_img_m_url }}">
                                                            瀏覽
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-delete-image"
                                                                data-url="{{ route('admin.advert.delete-image', $advert->adv_id) }}"
                                                                data-field="adv_img_m_url">
                                                            刪除
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            <div id="stats-adv_img_m_url" class="mt-1 small text-secondary upload-stats"></div>
                                        </div>
                                    </div>

                                    {{-- 連結設定 --}}
                                    <div class="form-row field field-adv_link_url d-none">
                                        <div class="col-md-12 form-group">
                                            <label for="adv_link_url">廣告跳轉連結</label>
                                            <input type="url" id="adv_link_url" name="adv_link_url" class="form-control"
                                                   placeholder="https://..." value="{{ old('adv_link_url', $advert->adv_link_url ?? '') }}">
                                            <small class="text-muted">請輸入包含 http(s):// 的完整網址</small>
                                        </div>
                                    </div>

                                    {{-- 顯示狀態與排序 --}}
                                    <div class="form-row border-top pt-3">
                                        <div class="col-md-4 form-group">
                                            <label for="display_order">顯示排序</label>
                                            <input type="number" id="display_order" name="display_order" class="form-control"
                                                   value="{{ old('display_order', $advert->display_order ?? 0) }}">
                                        </div>
                                        <div class="col-md-4 form-group px-md-5">
                                            <label>是否前台顯示</label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_visible"
                                                       name="is_visible" value="1" {{ old('is_visible', $advert->is_visible ?? 1) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="is_visible">啟動顯示</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 其他／預覽頁籤 --}}
                        <div class="tab-pane fade p-3" id="tab-preview" role="tabpanel">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-info"></i> 預覽說明</h5>
                                <p>存檔後，您可以至前台對應分類區塊確認廣告呈現效果。</p>
                            </div>
                        </div>
                    </x-slot:content>

                    {{-- 3. 底部動作按鈕 --}}
                    <x-slot:footer>
                        <a href="{{ route('admin.advert.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-chevron-left mr-1"></i>返回列表
                        </a>
                        <button type="submit" class="btn btn-success px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i>{{ $isEdit ? '儲存更新' : '確認新增' }}
                        </button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>

@stop

@section('js')
    <script>
        /**
         * 廣告管理模組表單邏輯
         * 負責：分類連動欄位、圖片預覽、表單防呆
         */
        $(function() {
            const $form = $('form[name="the-form"]');
            const $catSelect = $('#cat_id');

            // --- 1. 分類連動邏輯 (Category Scope) ---

            function updateFieldScope() {
                const $selected = $catSelect.find('option:selected');
                const funcScope = $selected.data('func') || [];
                const params = $selected.data('params') || {};

                // 先隱藏所有動態欄位並清空提示
                $('.field').addClass('d-none');
                $('.field-hint').text('');

                // 根據選取的分類開啟對應欄位
                funcScope.forEach(fieldName => {
                    const $fieldContainer = $(`.field-${fieldName}`);
                    if ($fieldContainer.length) {
                        $fieldContainer.removeClass('d-none');

                        // 注入建議尺寸提示 (如果後端有傳 params)
                        const config = params.fields ? params.fields[fieldName] : null;
                        if (config && config.width) {
                            $fieldContainer.find('.field-hint').text(`💡 建議尺寸：${config.width} x ${config.height} px`);
                        }
                    }
                });
            }

            // 初始化與變更事件觸發
            $catSelect.on('change', updateFieldScope);
            updateFieldScope();

            // --- 3. 表單防呆與提交 ---

            $form.on('submit', function(e) {
                // 調用全域通用驗證函數 (需確認 admin 專案中有定義 validateRequiredFields)
                if (typeof validateRequiredFields === "function" && !validateRequiredFields(this)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@stop
