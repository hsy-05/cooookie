@extends('adminlte::page')

@section('title', $pageTitle)

@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        <form action="{{ $isEdit ? route('admin.advert_category.update', $category->cat_id) : route('admin.advert_category.store') }}"
              method="POST" id="advCatForm">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div class="col-md-12">
                <x-admin.card-tabs>
                    {{-- 第一層頁籤：區分基礎代碼與多語系名稱 --}}
                    <x-slot:tabs>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-system" role="tab">系統參數 (工程師專用)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-i18n" role="tab">多語系顯示名稱</a>
                        </li>
                    </x-slot:tabs>

                    <x-slot:content>
                        {{-- 系統參數頁籤 --}}
                        <div class="tab-pane fade show active" id="tab-system" role="tabpanel">
                            <div class="p-3">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="cat_code">分類識別代碼 (cat_code)</label>
                                        <input type="text" id="cat_code" name="cat_code"
                                               class="form-control" value="{{ old('cat_code', $category->cat_code) }}"
                                               placeholder="例如：HOME_BANNER" required>
                                        <small class="text-muted">這是給程式抓取資料用的 ID，請勿隨意更動。</small>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="display_order">排序</label>
                                        <input type="number" id="display_order" name="display_order"
                                               class="form-control" value="{{ old('display_order', $category->display_order ?? 0) }}">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="is_visible">是否顯示</label>
                                        <select name="is_visible" id="is_visible" class="form-control">
                                            <option value="1" {{ old('is_visible', $category->is_visible) == 1 ? 'selected' : '' }}>顯示</option>
                                            <option value="0" {{ old('is_visible', $category->is_visible) == 0 ? 'selected' : '' }}>隱藏</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>啟用廣告欄位 (cat_func_scope)</label>
                                    <div class="d-flex flex-wrap border rounded p-3 bg-light">
                                        {{-- 這些選項原本在 @php 內，現在改為直接渲染，保持邏輯清晰 --}}
                                        <div class="custom-control custom-checkbox mr-4">
                                            <input class="custom-control-input" type="checkbox" name="cat_func_scope[]"
                                                   id="scope_img" value="adv_img_url"
                                                   {{ in_array('adv_img_url', (array)$category->cat_func_scope) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="scope_img">電腦版圖片 (adv_img_url)</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mr-4">
                                            <input class="custom-control-input" type="checkbox" name="cat_func_scope[]"
                                                   id="scope_img_m" value="adv_img_m_url"
                                                   {{ in_array('adv_img_m_url', (array)$category->cat_func_scope) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="scope_img_m">手機版圖片 (adv_img_m_url)</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mr-4">
                                            <input class="custom-control-input" type="checkbox" name="cat_func_scope[]"
                                                   id="scope_link" value="adv_link_url"
                                                   {{ in_array('adv_link_url', (array)$category->cat_func_scope) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="scope_link">廣告連結 (adv_link_url)</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">勾選後，在編輯廣告內容時才會出現對應的輸入框。</small>
                                </div>

                                <div class="form-group">
                                    <label for="cat_params">擴充參數 (cat_params, JSON)</label>
                                    <textarea id="cat_params" name="cat_params" class="form-control text-monospace"
                                              rows="8" style="background: #272822; color: #f8f8f2;">{{ old('cat_params', json_encode($category->cat_params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                                    <small class="form-text text-info">
                                        可用於設定圖片建議寬高，例如：<code>{"fields":{"adv_img_url":{"width":1920,"height":600}}}</code>
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- 多語系名稱頁籤 --}}
                        <div class="tab-pane fade" id="tab-i18n" role="tabpanel">
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
                                                <label for="name_{{ $lang->lang_id }}">後台顯示名稱 ({{ $lang->code }})</label>
                                                <input type="text" id="name_{{ $lang->lang_id }}"
                                                       name="desc[{{ $lang->lang_id }}][cat_name]"
                                                       class="form-control"
                                                       value="{{ $descMap[$lang->lang_id]->cat_name ?? '' }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <a href="{{ route('admin.advert_category.index') }}" class="btn btn-secondary">返回列表</a>
                        <button type="submit" class="btn btn-success js-submit-btn">{{ $isEdit ? '更新分類' : '建立分類' }}</button>
                    </x-slot:footer>
                </x-admin.card-tabs>
            </div>
        </form>
    </x-admin.page-message>
@stop

@section('js')
    <script>
        /**
         * 廣告分類編輯頁面的專屬邏輯
         * 主要是為了防止 JSON 格式錯誤導致後端 crash
         */
        $(function() {
            const $form = $('#advCatForm');
            const $jsonInput = $('#cat_params');

            // 送出前的最後檢查
            $form.on('submit', function(e) {
                const jsonVal = $jsonInput.val().trim();

                // 如果內容不是空的，就檢查一下是不是合法的 JSON 格式
                if (jsonVal !== '') {
                    try {
                        JSON.parse(jsonVal);
                    } catch (error) {
                        e.preventDefault();
                        alert('JSON 格式有誤，請檢查括號或逗號是否正確。');
                        $jsonInput.focus();
                        return false;
                    }
                }
            });
        });
    </script>
@stop
