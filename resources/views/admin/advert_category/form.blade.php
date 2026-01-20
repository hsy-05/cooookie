@extends('adminlte::page')

@section('title', isset($isEdit) && $isEdit ? '編輯廣告分類' : '新增廣告分類')

@include('components.admin.page_content_header')

@section('content')
    <form
        action="{{ isset($isEdit) && $isEdit ? route('admin.advert_category.update', $advert_category->cat_id) : route('admin.advert_category.store') }}"
        method="POST">
        @csrf
        @if (isset($isEdit) && $isEdit)
            @method('PUT')
        @endif

        <!-- 表單頁籤 -->
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="role-tab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#general">一般資料</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#preview">其他／預覽</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="form-tabs-content">
                    {{-- 一般資料 --}}
                    <div id="general" class="tab-pane fade show active p-3" role="tabpanel">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>分類代碼 (cat_code)</label>
                                <input type="text" name="cat_code" class="form-control"
                                    value="{{ old('cat_code', $advert_category->cat_code) }}" required>
                            </div>

                            <div class="form-group col-md-2 ml-3">
                                <label>排序 (sort_order)</label>
                                <input type="number" name="sort_order" class="form-control"
                                    value="{{ old('sort_order', $advert_category->sort_order) }}">
                            </div>

                            <div class="form-group col-md-5 ml-3">
                                <label for="is_visible">是否顯示</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_visible" name="is_visible"
                                        value="1"
                                        {{ isset($isEdit) && $advert_category->is_visible ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_visible"></label>
                                </div>
                            </div>
                        </div>


                        {{-- cat_func_scope：以 checkbox 呈現，送出 array --}}
                        <div class="form-group">
                            <label>啟用欄位 (cat_func_scope)</label>
                            @php
                                $scope = old('cat_func_scope', $advert_category->cat_func_scope ?? []);
                                $opts = [
                                    'adv_img_url' => '電腦版圖片',
                                    'adv_img_m_url' => '手機版圖片',
                                    'adv_link_url' => '廣告連結',
                                ];
                            @endphp
                            <div class="form-check">
                                @foreach ($opts as $k => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="cat_func_scope[]"
                                            value="{{ $k }}"
                                            {{ in_array($k, (array) $scope, true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="scope_{{ $k }}">{{ $label }}
                                            ({{ $k }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">決定此分類底下廣告表單會出現哪些欄位。</small>
                        </div>

                        {{-- cat_params：以 JSON 字串輸入，後端驗證 json --}}
                        <div class="form-group">
                            <label>分類參數 (cat_params, JSON)</label>
                            <textarea name="cat_params" class="form-control" rows="8">{{ old('cat_params', json_encode($advert_category->cat_params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                            <small class="form-text text-muted">
                                例如：<code>{"item_limit_num":-1,"fields":{"adv_img_url":{"width":1920,"height":960},"adv_img_m_url":{"width":800,"height":960}}}</code>
                            </small>
                        </div>
                    </div>

                    {{-- 多語名稱 --}}
                    <div id="i18n" class="tab-pane fade p-3" role="tabpanel">
                        <ul class="nav nav-tabs mt-2" role="tablist">
                            @foreach ($langs as $lang)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                        href="#lang-{{ $lang->lang_id }}">
                                        {{ $lang->name }} ({{ $lang->code }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3">
                            @foreach ($langs as $lang)
                                @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="lang-{{ $lang->lang_id }}">
                                    <div class="form-group">
                                        <label>分類名稱 ({{ $lang->code }})</label>
                                        <input type="text" name="desc[{{ $lang->lang_id }}][cat_name]"
                                            class="form-control"
                                            value="{{ old("desc.{$lang->lang_id}.cat_name", $d->cat_name ?? '') }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('admin.advert_category.index') }}" class="btn btn-secondary">返回列表</a>
                <button type="submit" class="btn btn-success">{{ isset($isEdit) && $isEdit ? '更新' : '新增' }}</button>
            </div>
        </div>
    </form>
@stop
