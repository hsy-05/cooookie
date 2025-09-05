{{-- resources/views/admin/advert/form.blade.php --}}
@extends('adminlte::page')

@section('title', PAGE_TITLE)

@include('components.page_content_header')

@section('content')
    <x-page-message>
        <form id="advertForm"
            action="{{ isset($isEdit) && $isEdit ? route('admin.advert.update', $advert->adv_id) : route('admin.advert.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($isEdit) && $isEdit)
                @method('PUT')
            @endif

            <!-- 表單頁籤 -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" id="advert-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-general" data-toggle="tab" href="#general" role="tab"
                            aria-controls="general" aria-selected="true">一般資料</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-preview" data-toggle="tab" href="#preview" role="tab"
                            aria-controls="preview" aria-selected="false">其他／預覽</a>
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
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $lang->name }}
                                            ({{ $lang->code }})
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content" id="lang-tabs-content">
                                @foreach ($langs as $lang)
                                    @php $d = $descMap[$lang->lang_id] ?? null; @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="lang-{{ $lang->lang_id }}" role="tabpanel"
                                        aria-labelledby="lang-{{ $lang->lang_id }}-tab">

                                        <!-- 語系標題 -->
                                        <div class="form-group">
                                            <label>標題（{{ $lang->code }}）</label>
                                            <input type="text" name="desc[{{ $lang->lang_id }}][adv_name]"
                                                class="form-control"
                                                value="{{ old('desc.' . $lang->lang_id . '.adv_name', $d->adv_name ?? '') }}">
                                        </div>

                                        <!-- 語系圖片（如需要不同語系圖片，可取消註解） -->
                                        {{-- <div class="form-group">
                                            <label>圖片（{{ $lang->code }}）</label>
                                            <input type="file" name="desc[{{ $lang->lang_id }}][image]"
                                                   class="form-control">
                                        </div> --}}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 共同設定區 -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>共同設定</h5>
                            </div>
                            <div class="card-body">

                                <!-- 分類獨立一行，寬度 col-md-6 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="cat_id">分類</label>
                                        <select name="cat_id" id="cat_id" class="form-control" required>
                                            <option value="">-- 選擇分類 --</option>
                                            @foreach ($cats as $cat)
                                                <option value="{{ $cat->cat_id }}"
                                                    data-func='@json($cat->cat_func_scope)'
                                                    data-params='@json($cat->cat_params)'
                                                    {{ old('cat_id', $advert->cat_id ?? '') == $cat->cat_id ? 'selected' : '' }}>
                                                    {{ $cat->cat_code }}
                                                    @if (optional($cat->descs->first())->cat_name)
                                                        - {{ optional($cat->descs->first())->cat_name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- 圖片區塊 -->
                                <div class="row">
                                    <!-- 電腦版圖片 -->
                                    <div class="col-md-6 mb-4 field field-adv_img_url">
                                        <label>廣告圖-電腦版</label>
                                        <div class="input-group">
                                            <input type="file" name="adv_img_url" class="form-control">
                                            @if (isset($isEdit) && $advert->adv_img_url)
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info btn-browse-image"
                                                        data-image="{{ $UPLOAD_PATH . '/' . $advert->adv_img_url }}">
                                                        瀏覽
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted field-hint"></small>
                                        <img class="preview-img img-fluid mt-2 d-none" alt="預覽">
                                    </div>

                                    <!-- 手機版圖片 -->
                                    <div class="col-md-6 mb-4 field field-adv_img_m_url">
                                        <label>廣告圖-手機版</label>
                                        <div class="input-group">
                                            <input type="file" name="adv_img_m_url" class="form-control">
                                            @if (isset($isEdit) && $advert->adv_img_m_url)
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info btn-browse-image"
                                                        data-image="{{ $UPLOAD_PATH . '/' . $advert->adv_img_m_url }}">
                                                        瀏覽
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted field-hint"></small>
                                        <img class="preview-img img-fluid mt-2 d-none" alt="預覽">
                                    </div>
                                </div>

                                <!-- 廣告連結 -->
                                <div class="row">
                                    <div class="col-md-12 mb-4 field field-adv_link_url">
                                        <label>廣告連結</label>
                                        <input type="url" name="adv_link_url" class="form-control"
                                            value="{{ old('adv_link_url', $advert->adv_link_url ?? '') }}">
                                        <small class="form-text text-muted field-hint">請輸入完整網址 (https://...)</small>
                                    </div>
                                </div>

                                <!-- 排序與是否顯示 -->
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label>排序</label>
                                        <input type="number" name="display_order" class="form-control"
                                            value="{{ old('display_order', $advert->display_order ?? 0) }}">
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label>是否顯示</label>
                                        <select name="is_visible" class="form-control">
                                            <option value="1"
                                                {{ old('is_visible', $advert->is_visible ?? 1) ? 'selected' : '' }}>顯示
                                            </option>
                                            <option value="0"
                                                {{ !old('is_visible', $advert->is_visible ?? 1) ? 'selected' : '' }}>隱藏
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 第二頁籤：其他／預覽 -->
                    <div class="tab-pane fade p-3" id="preview" role="tabpanel" aria-labelledby="tab-preview">
                        <p class="text-muted">這邊可以擺放預覽功能或其他進階設定區塊。</p>
                    </div>
                </div>

                <!-- 提交按鈕 -->
                <div class="text-right mt-3">
                    <a href="{{ route('admin.advert.index') }}" class="btn btn-secondary">返回</a>
                    <button type="submit" class="btn btn-success">
                        {{ isset($isEdit) ? '更新' : '新增' }}
                    </button>
                </div>
        </form>
    </x-page-message>

    <!-- 共用 圖片預覽 Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">圖片預覽</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="關閉">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="imageModalImg" src="" class="img-fluid" alt="圖片">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        (function($) {
            function parseJSONRaw(v) {
                if (!v) return null;
                if (typeof v === 'object') return v;
                try {
                    return JSON.parse(v);
                } catch (e) {
                    return null;
                }
            }

            function applyCategoryScope(option) {
                $('.field').addClass('d-none');
                $('.field-hint').text('');
                if (!option || !option.value) return;
                const func = parseJSONRaw($(option).attr('data-func')) || [];
                const params = parseJSONRaw($(option).attr('data-params')) || {};
                func.forEach(name => {
                    const $el = $('.field-' + name);
                    if ($el.length) {
                        $el.removeClass('d-none');
                        const hint = params.fields?.[name];
                        if (hint?.width && hint?.height) {
                            $el.find('.field-hint').text(`建議尺寸：${hint.width} x ${hint.height}`);
                        }
                    }
                });
            }

            $(function() {
                applyCategoryScope($('#cat_id option:selected')[0]);
                $('#cat_id').on('change', function() {
                    applyCategoryScope(this.options[this.selectedIndex]);
                });
                $(document).on('click', '.btn-browse-image', function() {
                    const img = $(this).data('image');
                    if (!img) return alert('此項目尚無圖片可預覽');
                    $('#imageModalImg').attr('src', img);
                    $('#imageModal').modal('show');
                });
                $(document).on('change', 'input[type=file]', function() {
                    const file = this.files?.[0];
                    const $preview = $(this).closest('.field').find('.preview-img');
                    if (!file) {
                        return $preview.addClass('d-none').attr('src', '');
                    }
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        $preview.removeClass('d-none').attr('src', evt.target.result);
                    };
                    reader.readAsDataURL(file);
                });
            });
        })(jQuery);
    </script>
@stop
