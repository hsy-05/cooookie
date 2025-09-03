@extends('adminlte::page')

@section('title', isset($isEdit) && $isEdit ? '編輯廣告' : '新增廣告')

@section('content_header')
    <h1>{{ isset($isEdit) && $isEdit ? '編輯廣告' : '新增廣告' }}</h1>
@stop

@section('content')
    <form id="advertForm"
        action="{{ isset($isEdit) ? route('admin.advert_category.update', $advert_category->cat_id) : route('admin.advert_category.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($isEdit))
            @method('PUT')
        @endif

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>分類</label>
                <select name="cat_id" id="cat_id" class="form-control" required>
                    <option value="">-- 選擇分類 --</option>
                    @foreach ($categoryList as $cat)
                        <option value="{{ $cat->cat_id }}" data-func='@json($cat->cat_func_scope)'
                            data-params='@json($cat->cat_params)'
                            {{ old('cat_id', $advert_category->cat_id ?? '') == $cat->cat_id ? 'selected' : '' }}>
                            {{ $cat->cat_code }}{{ $cat->descs()->first()?->cat_name ? ' - ' . $cat->descs()->first()->cat_name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-2">
                <label>排序</label>
                <input type="number" name="display_order" class="form-control"
                    value="{{ old('display_order', $advert_category->display_order ?? 0) }}">
            </div>

            <div class="form-group col-md-2">
                <label>是否顯示</label>
                <select name="is_visible" class="form-control">
                    <option value="1" {{ old('is_visible', $advert_category->is_visible ?? 1) ? 'selected' : '' }}>顯示
                    </option>
                    <option value="0" {{ !old('is_visible', $advert_category->is_visible ?? 1) ? 'selected' : '' }}>隱藏
                    </option>
                </select>
            </div>
        </div>

        <hr>

        {{-- adv_img_url 欄位（電腦圖） --}}
        <div id="field_adv_img_url" class="form-group" style="display:none;">
            <label>電腦版圖片（adv_img_url）</label>
            <input type="file" name="adv_img_url" class="form-control-file">
            <small id="hint_adv_img_url" class="form-text text-muted"></small>

            @if (isset($advert_category->adv_img_url) && $advert_category->adv_img_url)
                <div class="mt-2">
                    <img src="{{ asset($advert_category->adv_img_url) }}" alt="電腦圖" style="max-width:320px;">
                </div>
            @endif
        </div>

        {{-- adv_img_m_url 欄位（手機圖） --}}
        <div id="field_adv_img_m_url" class="form-group" style="display:none;">
            <label>手機版圖片（adv_img_m_url）</label>
            <input type="file" name="adv_img_m_url" class="form-control-file">
            <small id="hint_adv_img_m_url" class="form-text text-muted"></small>

            @if (isset($advert_category->adv_img_m_url) && $advert_category->adv_img_m_url)
                <div class="mt-2">
                    <img src="{{ asset($advert_category->adv_img_m_url) }}" alt="手機圖" style="max-width:180px;">
                </div>
            @endif
        </div>

        {{-- adv_link_url 欄位（連結） --}}
        <div id="field_adv_link_url" class="form-group" style="display:none;">
            <label>廣告連結（adv_link_url）</label>
            <input type="url" name="adv_link_url" class="form-control"
                value="{{ old('adv_link_url', $advert_category->adv_link_url ?? '') }}">
            <small class="form-text text-muted">輸入完整網址 (https://...)。</small>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">儲存</button>
            <a href="{{ route('admin.advert_category.index') }}" class="btn btn-secondary">返回列表</a>
        </div>
    </form>
@stop

@section('js')
    <script>
        // 載入頁面時，解析 select option 的 data-func / data-params
        (function() {
            function parseJSON(v) {
                try {
                    return typeof v === 'string' ? JSON.parse(v) : v;
                } catch (e) {
                    return null;
                }
            }

            // 依 function scope 顯示/隱藏欄位，同時顯示 hint（若 cat_params 提供寬高）
            function applyCategoryScope(optionEl) {
                // hide all first
                document.getElementById('field_adv_img_url').style.display = 'none';
                document.getElementById('field_adv_img_m_url').style.display = 'none';
                document.getElementById('field_adv_link_url').style.display = 'none';

                if (!optionEl || !optionEl.value) return;

                let func = parseJSON(optionEl.getAttribute('data-func')) || [];
                let params = parseJSON(optionEl.getAttribute('data-params')) || {};

                // show fields based on func array
                if (func.includes('adv_img_url')) {
                    document.getElementById('field_adv_img_url').style.display = 'block';
                    // show hint if params has fields->adv_img_url->width/height
                    const hint = (params.fields && params.fields.adv_img_url) ? params.fields.adv_img_url : null;
                    document.getElementById('hint_adv_img_url').textContent = hint ?
                        `建議尺寸：${hint.width} x ${hint.height}` : '';
                } else {
                    document.getElementById('hint_adv_img_url').textContent = '';
                }

                if (func.includes('adv_img_m_url')) {
                    document.getElementById('field_adv_img_m_url').style.display = 'block';
                    const hint2 = (params.fields && params.fields.adv_img_m_url) ? params.fields.adv_img_m_url : null;
                    document.getElementById('hint_adv_img_m_url').textContent = hint2 ?
                        `建議尺寸：${hint2.width} x ${hint2.height}` : '';
                } else {
                    document.getElementById('hint_adv_img_m_url').textContent = '';
                }

                if (func.includes('adv_link_url')) {
                    document.getElementById('field_adv_link_url').style.display = 'block';
                }
            }

            const sel = document.getElementById('cat_id');
            // initial apply (support old value)
            applyCategoryScope(sel.options[sel.selectedIndex]);

            sel.addEventListener('change', function(e) {
                applyCategoryScope(e.target.options[e.target.selectedIndex]);
            });
        })();
    </script>
@stop
