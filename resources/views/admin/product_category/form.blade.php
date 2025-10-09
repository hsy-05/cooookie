@extends('adminlte::page')

@section('title', '編輯分類')

@section('content_header')
    <h1>{{ isset($isEdit) ? '編輯分類' : '新增分類' }}</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <!-- 📄 Summernote 範本插入 Modal -->
    @include('components.summernote.template-modal')

    <form action="{{ isset($isEdit) ? route('admin.product_category.update', $product_category->cat_id) : route('admin.product_category.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($isEdit))
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
                    <!-- 多語系「名稱」輸入 -->
                    <div class="nav-tabs-custom mt-3">
                        <ul class="nav nav-tabs" id="language-tabs" role="tablist">
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
                                @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                    aria-labelledby="lang-{{ $lang->lang_id }}-tab">
                                    <div class="form-group">
                                        <label for="name_{{ $lang->lang_id }}">分類名稱</label>
                                        <input type="text" id="name_{{ $lang->lang_id }}"
                                            name="desc[{{ $lang->lang_id }}][name]" class="form-control"
                                            value="{{ $d->name ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="description_{{ $lang->lang_id }}">簡述</label>
                                        <input type="text" id="description_{{ $lang->lang_id }}"
                                            name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                            value="{{ $d->description ?? '' }}">
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
                                <div class="col-md-6 form-group">
                                    <label for="parent_id">父類 (Parent)</label>
                                    <select id="parent_id" name="parent_id" class="form-control">
                                        <option value="">無</option>
                                        @foreach ($parents as $p)
                                            <option value="{{ $p->cat_id }}"
                                                {{ isset($isEdit) && $p->cat_id == $product_category->parent_id ? 'selected' : '' }}>
                                                ID {{ $p->cat_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="is_visible">是否顯示</label>
                                    <select id="is_visible" name="is_visible" class="form-control">
                                        <option value="1" {{ isset($isEdit) && $product_category->is_visible ? 'selected' : '' }}>顯示</option>
                                        <option value="0" {{ isset($isEdit) && !$product_category->is_visible ? 'selected' : '' }}>隱藏</option>
                                    </select>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="display_order">排序</label>
                                    <input type="number" id="display_order" name="display_order" class="form-control"
                                        value="{{ isset($isEdit) ? $product_category->display_order : 0 }}">
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
                                    href="#content-{{ $lang->lang_id }}">{{ $lang->name }} ({{ $lang->code }})</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3">
                        @foreach ($langs as $lang)
                            @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="content-{{ $lang->lang_id }}">
                                <div class="form-group">
                                    <textarea name="desc[{{ $lang->lang_id }}][content]" class="form-control summernote">{{ $d->content ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按鈕 -->
        <div class="text-right mt-3">
            <a href="{{ route('admin.product_category.index') }}" class="btn btn-secondary">返回</a>
            <button type="submit" class="btn btn-success">{{ isset($isEdit) ? '更新' : '新增' }}</button>
        </div>
    </form>
@stop

@section('js')
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

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
