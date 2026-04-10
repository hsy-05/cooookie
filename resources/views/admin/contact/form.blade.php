@extends('adminlte::page')

@section('title', $pageTitle)

{{-- 引入頁面標頭組件，用於顯示麵包屑與標題 --}}
@include('components.admin.page_content_header')

@section('content')
    <x-admin.page-message>
        <div class="container-fluid">

            {{-- 上方區域：分為左右兩欄，左邊顯示原始諮詢，右邊顯示往返紀錄 --}}
            <div class="row">

                {{-- 左側：諮詢內容資訊卡片 (佔比 5/12) --}}
                <div class="col-xl-5 col-lg-5 mb-4">
                    <div class="card h-100 shadow-sm border-left-primary">
                        <div class="card-header bg-white d-flex align-items-center">
                            <h3 class="card-title font-weight-bold text-primary">
                                <i class="fas fa-question-circle mr-2"></i>諮詢詳情
                            </h3>
                            <div class="card-tools ml-auto">
                                {{-- 狀態標籤渲染：待處理顯示警告色，已回覆顯示成功色 --}}
                                @if ($contact->status === 'replied')
                                    <span class="badge badge-success px-3">已回覆</span>
                                @else
                                    <span class="badge badge-warning px-3">待處理</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- 使用定義列表 (dl) 呈現客戶資訊，語意化更佳 --}}
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted font-weight-normal">諮詢編號</dt>
                                <dd class="col-sm-8 text-bold text-monospace">{{ $contact->contact_sn }}</dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">聯絡姓名</dt>
                                <dd class="col-sm-8">{{ $contact->fullname }}</dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">電子郵件</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:{{ $contact->email }}"
                                        class="text-info text-decoration-none">{{ $contact->email }}</a>
                                </dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">諮詢主旨</dt>
                                <dd class="col-sm-8 font-weight-bold text-dark">{{ $contact->subject }}</dd>

                                <dt class="col-sm-4 text-muted font-weight-normal">諮詢時間</dt>
                                <dd class="col-sm-8 text-secondary">{{ $contact->created_at->format('Y-m-d H:i:s') }}</dd>
                            </dl>

                            <hr class="my-3">

                            <label class="text-muted"><i class="fas fa-comment-dots mr-1"></i> 客戶訊息內容</label>
                            {{-- 利用 Bootstrap padding 與背景類別取代 style，內容區域保持適當間距 --}}
                            <div class="p-3 bg-light rounded border text-secondary lh-1-6 min-h-150">
                                {!! nl2br(e($contact->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 右側：歷史回覆紀錄 (佔比 7/12，讓對話視窗較寬易於閱讀) --}}
                <div class="col-xl-7 col-lg-7 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold text-info">
                                <i class="fas fa-history mr-2"></i>回覆歷史紀錄
                            </h3>
                        </div>
                        {{-- 移除內聯 style，改用類別控制溢出與高度 --}}
                        <div class="card-body overflow-auto contact-history-area">
                            <div class="timeline timeline-inverse">
                                @forelse($contact->replies as $reply)
                                    <div class="mb-3">
                                        <i class="fas fa-reply bg-info shadow-sm"></i>
                                        <div class="timeline-item border shadow-none bg-light">
                                            <span class="time text-muted">
                                                <i
                                                    class="far fa-clock mr-1"></i>{{ $reply->created_at->format('Y-m-d H:i') }}
                                            </span>
                                            <h3 class="timeline-header no-border font-weight-bold">
                                                處理人員：{{ $reply->admin->name ?? '管理員' }}
                                            </h3>
                                            <div class="timeline-body small text-dark">
                                               {!! $reply->content !!}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    {{-- 無紀錄時的空狀態提示 --}}
                                    <div class="text-center py-5 text-muted opacity-7">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">目前尚無任何回覆紀錄</p>
                                    </div>
                                @endforelse
                                {{-- 時間軸底部結點 --}}
                                <div><i class="far fa-dot-circle bg-gray"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 下方區域：回覆編輯區 --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-top-success">
                        <div class="card-header bg-dark">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-pen-nib mr-2"></i>執行回覆操作
                            </h3>
                        </div>
                        {{-- 表單提交至 update 路由，並設定 name 供 JS 抓取 --}}
                        <form action="{{ route('admin.contact.update', $contact->contact_id) }}" method="POST"
                            name="the-form" id="replyForm">
                            @csrf
                            @method('PUT')
                            {{-- 返回網址 --}}
                            <input type="hidden" name="back_url" value="{{ $backUrl ?? route('admin.contact.index') }}">

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="reply_content" class="font-weight-bold">回覆內容</label>
                                    {{-- 載入 Summernote 套件，並設定必填 --}}
                                    <textarea name="reply_content" id="reply_content" class="form-control summernote required-field"
                                        placeholder="請輸入欲回覆給客戶的詳細內容..."></textarea>
                                </div>

                                <div class="form-group mb-0">
                                    {{-- 使用 Switch 樣式開關取代傳統 Checkbox，提升介面現代感 --}}
                                    <div
                                        class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                        <input type="checkbox" class="custom-control-input" id="send_mail" name="send_mail"
                                            value="1" checked>
                                        <label class="custom-control-label font-weight-normal" for="send_mail">
                                            同步發送 Email 通知客戶 <span class="text-xs text-muted">(系統將自動寄送副本至客戶信箱)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- 底部按鈕區 --}}
                            <div class="card-footer table-actions-container">
                                {{-- 返回列表按鈕 --}}
                                <a href="{{ $backUrl ?? route('admin.news.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>返回列表
                                </a>
                                {{-- 送出按鈕 --}}
                                <button type="submit" class="btn btn-success js-submit-btn">
                                    <i class="fas fa-paper-plane mr-1"></i>確認送出回覆
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </x-admin.page-message>
@stop

{{-- 針對無法用 Bootstrap 類別完全取代的細節，撰寫於 CSS 區塊而非內聯 --}}
@push('css')
@endpush

@push('js')
    {{-- 引入 Summernote 相關資源組件 --}}
    @include('components.admin.summernote._summernote')
@endpush

@section('js')
    <script>
        /**
         * 處理回覆表單的提交邏輯與驗證
         */
        $(function() {
            // 當表單提交時執行的檢查程序
            $('form[name="the-form"]').on('submit', function(e) {

                // 呼叫 common.js 的基礎驗證，確保必填欄位不為空
                if (typeof validateRequiredFields === "function" && !validateRequiredFields(this)) {
                    e.preventDefault();
                    return false;
                }

                // 在送出前將 Summernote 的內容同步回原始隱藏的 textarea
                if (typeof syncSummernoteContent === "function") {
                    syncSummernoteContent('form[name="the-form"]');
                }
            });
        });
    </script>
@stop
