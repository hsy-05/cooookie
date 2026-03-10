@extends('adminlte::page')

@section('title', '系統參數設定')

@section('content')
<section class="system-settings-container container-fluid py-4">
    <x-admin.page-message>
        <form id="the-form" action="{{ route('admin.system_settings.update_all') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <x-admin.card-tabs>
                <x-slot:tabs>
                    @foreach ($tabs as $tab)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="pill" href="#tab-{{ $tab->id }}" role="tab">
                                {{ $tab->title }}
                            </a>
                        </li>
                    @endforeach
                </x-slot:tabs>

                <x-slot:content>
                    @foreach ($tabs as $tab)
                        <article class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tab->id }}" role="tabpanel">
                            <div class="row p-2 p-md-3">
                                @foreach ($tab->children as $item)
                                    <div class="col-12 mb-2">
                                        <div class="setting-item-card h-100 p-3 border rounded shadow-sm">
                                            <header class="setting-header mb-3">
                                                <label class="setting-title d-block font-weight-bold mb-1">
                                                    {{ $item->title }}
                                                    <small class="text-secondary ml-1">#{{ $item->setting_key }}</small>
                                                </label>
                                                @if($item->description)
                                                    <p class="setting-desc text-muted small mb-0">{{ $item->description }}</p>
                                                @endif
                                            </header>

                                            <div class="setting-input-wrapper">
                                                @switch($item->type)
                                                {{-- 新增：語系下拉選單 --}}
                                                @case('lang')
                                                    <select name="settings[{{ $item->setting_key }}]" class="form-control">
                                                        @foreach($languages as $lang)
                                                            <option value="{{ $lang->lang_id }}"
                                                                {{ (string)$item->setting_value === (string)$lang->lang_id ? 'selected' : '' }}>
                                                                {{ $lang->name }} ({{ $lang->code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @break

                                                    {{-- 數字輸入 --}}
                                                    @case('number')
                                                        <input type="number" name="settings[{{ $item->setting_key }}]"
                                                            value="{{ $item->setting_value }}" class="form-control">
                                                        @break

                                                    {{-- 單選按鈕：直接使用 Model 的 options 屬性 --}}
                                                    @case('radio')
                                                        <div class="radio-group mt-2">
                                                            @foreach ($item->options as $val => $label)
                                                                <div class="custom-control custom-radio custom-control-inline">
                                                                    <input type="radio"
                                                                        id="{{ $item->setting_key . '_' . $val }}"
                                                                        name="settings[{{ $item->setting_key }}]"
                                                                        value="{{ $val }}"
                                                                        class="custom-control-input"
                                                                        {{ (string)$item->setting_value === (string)$val ? 'checked' : '' }}>
                                                                    <label class="custom-control-label font-weight-normal"
                                                                        for="{{ $item->setting_key . '_' . $val }}">{{ $label }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @break

                                                        {{-- 滑桿控制 --}}
                                                        @case('slider')
                                                        <div class="slider-wrapper">

                                                            <input
                                                                type="range"
                                                                name="settings[{{ $item->setting_key }}]"
                                                                value="{{ $item->setting_value ?? 50 }}"
                                                                min="{{ $item->min ?? 0 }}"
                                                                max="{{ $item->max ?? 100 }}"
                                                                step="{{ $item->step ?? 1 }}"
                                                                class="form-control-range js-range-input"
                                                            >

                                                            <div class="slider-value mt-2">
                                                                <span class="badge badge-primary js-range-value">
                                                                    {{ $item->setting_value ?? 50 }}
                                                                </span>
                                                            </div>

                                                        </div>
                                                        @break

                                                    {{-- 多行文字 --}}
                                                    @case('textarea')
                                                        <textarea name="settings[{{ $item->setting_key }}]"
                                                            class="form-control" rows="3">{{ $item->setting_value }}</textarea>
                                                        @break

                                                    {{-- 標籤輸入：修正跑版問題，移除多餘 Input --}}
                                                    @case('tags')
                                                    <div
                                                        class="js-tags-input"
                                                        data-name="settings[{{ $item->setting_key }}][]"
                                                        data-placeholder="請輸入標籤並按 Enter"
                                                    >
                                                        @foreach($item->tags_array as $tag)
                                                            <span class="tag-item" data-value="{{ $tag }}">{{ $tag }}</span>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                    {{-- 顏色選擇 --}}
                                                    @case('color')
                                                        <input type="color" name="settings[{{ $item->setting_key }}]"
                                                            value="{{ $item->setting_value ?? '#000000' }}"
                                                            class="form-control form-control-color">
                                                        @break

                                                    {{-- 圖片上傳 --}}
                                                    @case('image')
                                                        <div class="custom-file">
                                                            <input type="file" name="settings[{{ $item->setting_key }}]"
                                                                class="custom-file-input js-file-input" id="file_{{ $item->setting_key }}">
                                                            <label class="custom-file-label text-truncate" for="file_{{ $item->setting_key }}">
                                                                {{ $item->setting_value ? '已選取檔案' : '選擇圖片...' }}
                                                            </label>
                                                        </div>
                                                        @break

                                                    {{-- 預設文字輸入 --}}
                                                    @default
                                                        <input type="text" name="settings[{{ $item->setting_key }}]"
                                                            value="{{ $item->setting_value }}" class="form-control">
                                                @endswitch
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </x-slot:content>

                <x-slot:footer>
                    <div class="form-actions text-right p-2">
                        <button type="submit" class="btn btn-success js-submit-btn">
                            <i class="fas fa-save mr-2"></i>儲存設定
                        </button>
                    </div>
                </x-slot:footer>
            </x-admin.card-tabs>
        </form>
    </x-admin.page-message>
</section>
@stop

@push('js')
<script>
/**
 * 系統設定頁面邏輯
 */
(function ($) {
    "use strict";

    const SettingsModule = {
        /**
         * 入口初始化
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * 綁定監聽事件
         */
        bindEvents: function () {
            const self = this;

            // 處理檔案上傳顯示檔名 (修正 Bootstrap Custom File Input 不會自動顯示檔名的問題)
            $(document).on('change', '.js-file-input', function (e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : "選擇圖片...";
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        },
    };

    // DOM Ready 後啟動
    $(function () {
        SettingsModule.init();
    });

})(jQuery);
</script>
@endpush
