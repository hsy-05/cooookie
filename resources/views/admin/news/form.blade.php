@extends('adminlte::page')
@section('title', $pageTitle)

{{-- 麵包屑與標題組件 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        @include('components.summernote.template-modal')

        <form name="the-form" action="{{ $isEdit ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-12">
                <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs custom-styled-tabs" id="custom-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#tab-content" role="tab">內容設定</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-0">
                        <div class="tab-content" id="form-tabs-content">
                            {{-- 一般資料頁籤 --}}
                            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
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
                                                        value="{{ $descMap[$lang->lang_id]->title ?? '' }}">
                                                </div>
                                                <div class="form-group">
                                                    <label for="description_{{ $lang->lang_id }}">簡述</label>
                                                    <textarea id="description_{{ $lang->lang_id }}" name="desc[{{ $lang->lang_id }}][description]" class="form-control"
                                                        maxlength="25" rows="3" placeholder="最多 25 個字">{{ $descMap[$lang->lang_id]->description ?? '' }}</textarea>
                                                </div>
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
                                                    title="建議尺寸：{{ $fileConfigs['image_url']['width'] }} x {{ $fileConfigs['image_url']['height'] }} px
                                                            ，格式：JPG, PNG, WebP"></i>
                                                    @endif
                                                </label>

                                                <div class="input-group">
                                                    <input type="file" id="image_url" name="image_url" class="form-control image-upload-input">

                                                    {{-- 使用一個 ID 容器包裹按鈕，方便 AJAX 成功後直接隱藏 --}}
                                                    <div class="input-group-append {{ ($isEdit && $news->image_url) ? '' : 'd-none' }}" id="image-action-group">
                                                        @if ($isEdit && $news->image_url)
                                                            <button type="button" class="btn btn-info" id="btn-browse-image" data-toggle="modal" data-target="#imageModal">瀏覽</button>
                                                            <button type="button" class="btn btn-danger btn-delete-image"
                                                                    data-url="{{ route('admin.news.delete-image', $news->news_id) }}"
                                                                    data-field="image_url">刪除</button>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- 上傳進度與檔案資訊顯示區 --}}
                                                <div id="stats-image_url" class="mt-1 small text-secondary"></div>
                                            </div>

                                            {{-- 分類下拉選單 --}}
                                            <div class="col-md-6 form-group">
                                                <label for="cat_id">分類</label>
                                                <select id="cat_id" name="cat_id" class="form-control required-field">
                                                    <option value="">-- 無 --</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->cat_id }}" {{ $isEdit && $cat->cat_id == $news->cat_id ? 'selected' : '' }}>
                                                            {{ optional($cat->descs->first())->name ?? 'ID-' . $cat->cat_id }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 消息內容頁籤 --}}
                            <div class="tab-pane fade" id="tab-content" role="tabpanel">
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
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">返回</a>
                        <button type="submit" class="btn btn-success">{{ $isEdit ? '更新' : '新增' }}</button>
                    </div>
                </div>
            </div>
        </form>
    </x-admin.page-message>

    {{-- 圖片預覽 Modal --}}
    @if ($isEdit && $news->image_url)
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">圖片預覽</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ $UPLOAD_PATH . '/' . $news->image_url }}" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('js')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-zh-TW.min.js"></script>
    <script src="{{ asset('js/admin/summernote-init.js') }}"></script>

    <script>
        $(function() {
            // 1. 初始化 Tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // 2. 異步刪除圖片處理
            $('.btn-delete-image').on('click', function() {
                const btn = $(this);
                const url = btn.data('url');
                const field = btn.data('field');

                // 使用自訂的 showAlert 代替原生 confirm
                showAlert(
                    'warning',
                    '刪除確認',
                    '您確定要刪除這張封面圖片嗎？刪除後無法恢復。',
                    false,    // toast 模式關閉，顯示在正中間
                    'center', // 位置
                    true,     // 顯示確認按鈕
                    '確定刪除', // 確認按鈕文字
                    0,        // 不自動關閉
                    {
                        showCancelButton: true,
                        cancelButtonText: '取消',
                        preConfirm: () => {
                            // 在彈窗按下確定後，執行 AJAX 刪除
                            return executeDeleteImage(url, field, btn);
                        }
                    }
                );
            });

            /**
             * 執行 AJAX 刪除請求
             */
            function executeDeleteImage(url, field, btn) {
                // 按鈕進入讀取狀態
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                return $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        field: field
                    },
                    success: function(res) {
                        if (res.success) {
                            // 成功後的「不換頁」處理：
                            // 1. 隱藏瀏覽與刪除按鈕組
                            $('#image-action-group').addClass('d-none');
                            // 2. 清空檔案選擇器 (防止使用者剛好選了檔案又點刪除)
                            $(`#${field}`).val('');
                            // 3. 清空進度資訊
                            $(`#stats-${field}`).empty();

                            // 顯示成功提示
                            showAlert('success', '已刪除', '圖片已成功移除', true, 'top-end', false, '', 2000);
                        }
                    },
                    error: function(err) {
                        const errorMsg = err.responseJSON ? err.responseJSON.message : '系統錯誤';
                        showAlert('error', '刪除失敗', errorMsg, true, 'top-end', false, '', 3000);

                        // 恢復按鈕狀態
                        btn.prop('disabled', false).text('刪除');
                    }
                });
            }

            // 3. 檔案選取資訊更新
            $('.image-upload-input').on('change', function() {
                const file = this.files[0];
                const statsId = `#stats-${this.id}`;
                if (file) {
                    const kb = (file.size / 1024).toFixed(2);
                    $(statsId).html(`<i class="fas fa-check-circle text-success"></i> 已選取：${file.name} (${kb} KB)`);
                    // 如果使用者選了新檔案，建議隱藏舊的「瀏覽/刪除」按鈕避免混淆 (選擇性)
                    $('#image-action-group').addClass('d-none');
                }
            });

            // 4. 表單送出前的驗證
            $('form[name="the-form"]').on('submit', function(e) {
                if (typeof validateRequiredFields === "function" && !validateRequiredFields(this)) {
                    e.preventDefault();
                    return false;
                }
                if (typeof syncSummernoteContentOnSubmit === "function") {
                    syncSummernoteContentOnSubmit();
                }
            });
        });
    </script>
@stop
