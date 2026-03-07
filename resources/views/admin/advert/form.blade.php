@extends('adminlte::page')

@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        <form id="advertForm" name="the-form"
            action="{{ $isEdit ? route('admin.advert.update', $advert->adv_id) : route('admin.advert.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-12">
                <x-admin.card-tabs>
                    {{-- 頁籤定義 --}}
                    <x-slot:tabs>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-other" role="tab">其他設定</a>
                        </li>
                    </x-slot:tabs>

                    <x-slot:content>
                        <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

                            {{-- A. 多語系區塊 --}}
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
                                                       placeholder="請輸入內容......"
                                                       value="{{ old('desc.' . $lang->lang_id . '.adv_name', $descMap[$lang->lang_id]->adv_name ?? '') }}">
                                            </div>

                                            <div class="form-group field field-adv_subname d-none">
                                                <label for="adv_subname_{{ $lang->lang_id }}">廣告副標題 ({{ $lang->code }})</label>
                                                <input type="text"
                                                    id="adv_subname_{{ $lang->lang_id }}"
                                                    name="desc[{{ $lang->lang_id }}][adv_subname]"
                                                    class="form-control"
                                                    placeholder="請輸入內容......"
                                                    value="{{ old('desc.' . $lang->lang_id . '.adv_subname', $descMap[$lang->lang_id]->adv_subname ?? '') }}">
                                            </div>

                                                <div class="form-group field field-adv_brief d-none">
                                                    <label for="adv_brief_{{ $lang->lang_id }}">簡述</label>
                                                    <textarea id="adv_brief_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][adv_brief]" class="form-control"
                                                        maxlength="25" rows="3" placeholder="最多 25 個字">{{ $descMap[$lang->lang_id]->adv_brief ?? '' }}</textarea>
                                                </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- B. 共同設定區 --}}
                            <div class="card m-3 mt-0 shadow-none border">
                                <div class="card-header bg-light"><h5>共同設定</h5></div>
                                <div class="card-body">

                                    {{-- 分類選擇：驅動下方欄位顯示與尺寸提示更新 --}}
                                    <div class="form-row mb-3">
                                        <div class="col-md-6 form-group">
                                            <label for="cat_id">廣告分類</label>
                                            <select name="cat_id" id="cat_id" class="form-control required-field">
                                                <option value="">-- 請選擇分類 --</option>
                                                @foreach ($cats as $cat)
                                                    <option value="{{ $cat->cat_id }}"
                                                        data-func='@json($cat->cat_func_scope)'
                                                        data-params='@json($cat->cat_params)'
                                                        {{ (old('cat_id', $advert->cat_id ?? '') == $cat->cat_id) ? 'selected' : '' }}>
                                                        [{{ $cat->cat_code }}] {{ $cat->descs->first()->cat_name ?? '未命名分類' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        {{-- 1. 廣告圖 - 電腦版 --}}
                                        <div class="col-md-6 form-group field field-adv_img_url d-none">
                                            <label for="adv_img_url">
                                                廣告圖 (電腦版)
                                                {{-- 這裡維持與 News 一致的結構：PHP 負責初始渲染 --}}
                                                @if (isset($fileConfigs['adv_img_url']))
                                                <i class="fas fa-question-circle text-muted js-size-tooltip"
                                                   data-toggle="tooltip"
                                                   data-field="adv_img_url"
                                                   title="建議尺寸：{{ $fileConfigs['adv_img_url']['width'] }} x {{ $fileConfigs['adv_img_url']['height'] }} px，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>
                                            <div class="input-group">
                                                <input type="file" id="adv_img_url" name="adv_img_url" class="form-control" accept="image/*">
                                                @if ($isEdit && $advert->adv_img_url)
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info js-open-preview" data-url="{{ asset('storage/' . $advert->adv_img_url) }}">瀏覽</button>
                                                        <button type="button" class="btn btn-danger btn-delete-image" data-field="adv_img_url" data-id="{{ $advert->adv_id }}">刪除</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- 2. 廣告圖 - 手機版 --}}
                                        <div class="col-md-6 form-group field field-adv_img_m_url d-none">
                                            <label for="adv_img_m_url">
                                                廣告圖 (手機版)
                                                @if (isset($fileConfigs['adv_img_m_url']))
                                                <i class="fas fa-question-circle text-muted js-size-tooltip"
                                                   data-toggle="tooltip"
                                                   data-field="adv_img_m_url"
                                                   title="建議尺寸：{{ $fileConfigs['adv_img_m_url']['width'] }} x {{ $fileConfigs['adv_img_m_url']['height'] }} px，格式：JPG, PNG, WebP"></i>
                                                @endif
                                            </label>
                                            <div class="input-group">
                                                <input type="file" id="adv_img_m_url" name="adv_img_m_url" class="form-control" accept="image/*">
                                                @if ($isEdit && $advert->adv_img_m_url)
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info js-open-preview" data-url="{{ asset('storage/' . $advert->adv_img_m_url) }}">瀏覽</button>
                                                        <button type="button" class="btn btn-danger btn-delete-image" data-field="adv_img_m_url" data-id="{{ $advert->adv_id }}">刪除</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 連結設定 --}}
                                    <div class="form-row field field-adv_link_url d-none">
                                        <div class="col-md-12 form-group">
                                            <label for="adv_link_url">廣告跳轉連結</label>
                                            <input type="url" id="adv_link_url" name="adv_link_url" class="form-control"
                                                   placeholder="https://..." value="{{ old('adv_link_url', $advert->adv_link_url ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 其他設定 --}}
                        <div class="tab-pane fade p-3" id="tab-other" role="tabpanel">
                            <div class="form-row">
                                <div class="col-md-4 form-group">
                                    <label for="display_order">顯示排序</label>
                                    <input type="number" id="display_order" name="display_order" class="form-control"
                                           value="{{ old('display_order', $advert->display_order ?? 0) }}">
                                </div>
                                <div class="col-md-4 form-group px-md-5">
                                    <label>上架狀態</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_visible"
                                               name="is_visible" value="1" {{ old('is_visible', $advert->is_visible ?? 1) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_visible">啟動顯示</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <a href="{{ route('admin.advert.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-chevron-left mr-1"></i>返回
                        </a>
                        <button type="submit" class="btn btn-success px-4 shadow-sm float-right js-submit-btn">
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
         * 廣告管理表單邏輯
         */
        $(function() {
            const $catSelect = $('#cat_id');

            /**
             * 同步分類對應的欄位與 Tooltip 提示
             * 用途：確保切換分類時，欄位正確顯示且尺寸提示同步更新
             */
            function syncCategoryConfig() {
                const $selected = $catSelect.find('option:selected');
                if (!$selected.val()) return;

                const funcScope = $selected.data('func') || [];
                const params = $selected.data('params') || {};
                const fields = params.fields || {};

                // 隱藏所有動態欄位
                $('.field').addClass('d-none');

                // 遍歷該分類擁有的功能
                funcScope.forEach(fieldName => {
                    const $fieldContainer = $(`.field-${fieldName}`);
                    if ($fieldContainer.length) {
                        $fieldContainer.removeClass('d-none');

                        // 動態更新 Tooltip 的建議尺寸
                        const config = fields[fieldName];
                        if (config && config.width) {
                            const $tooltip = $fieldContainer.find('.js-size-tooltip');
                            if ($tooltip.length) {
                                const newTitle = `建議尺寸：${config.width} x ${config.height} px，格式：JPG, PNG, WebP`;

                                // 先銷毀舊的 Tooltip，更新內容後再重新初始化
                                $tooltip.attr('data-original-title', newTitle).tooltip();
                            }
                        }
                    }
                });
            }

            // 監聽分類切換事件
            $catSelect.on('change', syncCategoryConfig);

            // 頁面載入時初始化一次
            syncCategoryConfig();
        });
    </script>
@stop
