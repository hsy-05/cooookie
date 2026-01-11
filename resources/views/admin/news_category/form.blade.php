@extends('adminlte::page')

@section('title', $pageTitle)

@include('components.admin.page_content_header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/backend.css') }}">
@stop

@section('content')
    {{-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 --}}
    <x-admin.page-message>
        <!-- 📄 Summernote 範本插入 Modal -->
        @include('components.summernote.template-modal')

        <form
            action="{{ $isEdit ? route('admin.news_category.update', $category->cat_id) : route('admin.news_category.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <!-- 表單頁籤 -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" id="form-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab"
                            aria-controls="general" aria-selected="true">一般資料</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="content-tab" data-toggle="tab" href="#content" role="tab"
                            aria-controls="content" aria-selected="false">分類內容</a>
                    </li>
                </ul>

                <div class="tab-content" id="form-tabs-content">
                    <!-- 一般資料頁籤 -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                        <!-- 語系頁籤 -->
                        <div class="nav-tabs-custom mt-3">
                            <ul class="nav nav-tabs  mb-3" id="language-tabs" role="tablist">
                                @foreach ($langs as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="lang-{{ $lang->lang_id }}-tab" data-toggle="tab"
                                            href="#lang-{{ $lang->lang_id }}" role="tab"
                                            aria-controls="lang-{{ $lang->lang_id }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            {{ $lang->name }} ({{ $lang->code }})
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content" id="language-tabs-content">
                                @foreach ($langs as $lang)
                                    @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                        aria-labelledby="lang-{{ $lang->lang_id }}-tab">
                                        <div class="form-group">
                                            <label for="name_{{ $lang->lang_id }}">分類名稱</label>
                                            <input type="text" id="name_{{ $lang->lang_id }}"
                                                name="desc[{{ $lang->lang_id }}][name]" class="form-control"
                                                value="{{ $desc->name ?? '' }}">
                                        </div>
                                        <!-- 簡述欄位 -->
                                        <div class="form-group">
                                            <label for="description_{{ $lang->lang_id }}">簡述</label>
                                            <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                maxlength="25" rows="3" placeholder="最多 25 個字">{{ $desc->description ?? '' }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 共同設定 -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>共同設定</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-row">
                                    <div class="col-md-3 form-group">
                                        <label for="parent_id">父類 (Parent)</label>
                                        <select id="parent_id" name="parent_id" class="form-control">
                                            <option value="0">無 (最頂層)</option>
                                            @foreach ($parents as $p)
                                                {{--
                防呆邏輯：
                1. 如果該項目的 can_be_parent 為 false，則加上 disabled 禁止選取。
                2. 同時在名稱後面加註「(層級限制)」，對使用者更友善。
            --}}
                                                <option value="{{ $p->cat_id }}"
                                                    {{ old('parent_id', $category->parent_id ?? '') == $p->cat_id ? 'selected' : '' }}
                                                    {{ !$p->can_be_parent ? 'disabled' : '' }}>
                                                    {{ $p->name }}
                                                    {{ !$p->can_be_parent ? '(已達層級上限)' : '' }} (ID: {{ $p->cat_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">註：網站設定消息分類最高為
                                            {{ config('site_settings.category_levels.news') }} 層。</small>
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
                                        <input type="number" id="display_order" name="display_order" class="form-control"
                                            value="{{ $isEdit ? $category->display_order : 0 }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 分類內容頁籤 -->
                    <div class="tab-pane fade" id="content">
                        <!-- 語系內容的分頁 -->
                        <ul class="nav nav-tabs mt-2" role="tablist">
                            @foreach ($langs as $lang)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab"
                                        href="#content-{{ $lang->lang_id }}">{{ $lang->name }}
                                        ({{ $lang->code }})
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mt-3">
                            @foreach ($langs as $lang)
                                @php $desc = $descMap[$lang->lang_id] ?? null; @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="content-{{ $lang->lang_id }}">
                                    <div class="form-group">
                                        <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote">{{ $desc->content ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 提交按鈕 -->
            <div class="text-center mt-3">
                <a href="{{ route('admin.news_category.index') }}" class="btn btn-secondary">返回</a>
                <button type="submit" class="btn btn-success">{{ $isEdit ? '更新' : '新增' }}</button>
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
