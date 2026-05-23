@extends('frontend.layouts.app')

@push('styles')
    {{-- AOS 元素滾動動畫庫 CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    {{-- 聯絡我們頁面專屬版面樣式 --}}
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('content')
    <main id="contact-page">
        {{-- 頁面固定背景深色遮罩層 --}}
        <div class="page-fixed-bg">
            <div class="overlay-dark"></div>
        </div>

        {{-- 頁面主標題區塊 --}}
        <div class="c-banner-block" data-aos="fade-down">
            <h2 class="c-banner-title">聯絡我們</h2>
            <span class="c-banner-subtitle js-fade-up">Contact Us</span>
        </div>

        {{-- 表單輸入主要區域 --}}
        <section class="form-area" data-aos="fade-up">
            <header class="section-header text-center">
                <h3 class="section-title-text">感謝您對我們的支持，若有任何問題，歡迎與我們聯繫<br/>我們將盡快與您回覆，謝謝!</h3>
            </header>

            {{-- 防呆與彈性優化：將後端路由與金鑰透過 data 屬性傳遞給 JavaScript 讀取，避免前端硬編碼網址 --}}
            <form class="c-form" id="form_contact" autocomplete="off"
                  data-action="{{ route('contact.store') }}"
                  data-site-key="{{ config('services.recaptcha.site_key') }}">
                @csrf
                {{-- 存放 Google reCAPTCHA 驗證成功後生成的動態安全憑證 Token --}}
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                <div class="group-wrap">
                    {{-- 諮詢主題 --}}
                    <div class="group-half" data-aos="fade-up" data-aos-delay="100">
                        <label class="group-title">諮詢主題 <span class="important">*</span></label>
                        <div class="group-box">
                            <input type="text" name="subject" class="group-input" placeholder="您感興趣的主題" required>
                        </div>
                    </div>

                    {{-- 姓名與性別勾選 --}}
                    <div class="group-half" data-aos="fade-up" data-aos-delay="200">
                        <label class="group-title">姓名 <span class="important">*</span></label>
                        <div class="group-main-flex">
                            <div class="group-box name-input-box">
                                <input type="text" name="fullname" class="group-input" placeholder="您的姓名" required>
                            </div>
                            <div class="gender-group">
                                <label class="group-label">
                                    <input type="radio" name="sex" value="0" checked>
                                    <span class="icon"></span><span class="txt">先生</span>
                                </label>
                                <label class="group-label">
                                    <input type="radio" name="sex" value="1">
                                    <span class="icon"></span><span class="txt">女士</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 聯絡電話 --}}
                    <div class="group-half" data-aos="fade-up" data-aos-delay="250">
                        <label class="group-title">聯絡電話 <span class="important">*</span></label>
                        <div class="group-box">
                            <input type="tel" name="tel" class="group-input" placeholder="您的電話號碼" required>
                        </div>
                    </div>

                    {{-- 聯絡信箱 --}}
                    <div class="group-half" data-aos="fade-up" data-aos-delay="300">
                        <label class="group-title">聯絡信箱 <span class="important">*</span></label>
                        <div class="group-box">
                            <input type="email" name="email" class="group-input" placeholder="example@mail.com" required>
                        </div>
                    </div>

                    {{-- 聯絡地址 --}}
                    {{-- <div class="group-half" data-aos="fade-up" data-aos-delay="350">
                    <label class="group-title">聯絡地址</label>
                    <div class="group-box">
                        <input type="text" name="address" class="group-input" placeholder="您的收件地址">
                    </div>
                </div> --}}

                    {{-- 諮詢內容 --}}
                    <div class="group-full" data-aos="fade-up" data-aos-delay="350">
                        <label class="group-title">諮詢內容 <span class="important">*</span></label>
                        <div class="group-box">
                            <textarea name="content" class="group-textarea" placeholder="請詳細描述您的諮詢事項..." required></textarea>
                        </div>
                    </div>
                </div>

                {{-- 表單動作控制按鈕 --}}
                <div class="btn-box" data-aos="fade-up" data-aos-delay="350">
                    <button type="reset" class="button-style yellow2">重填資料</button>
                    <button type="submit" class="button-style black" id="btn-submit">確認送出</button>
                </div>

                {{-- 隱藏 reCAPTCHA 標籤時，根據 Google 規範必須揭露的隱私權條款文案 --}}
                <div class="recaptcha-policy-text" data-aos="fade-up" data-aos-delay="400">
                    This site is protected by reCAPTCHA and the Google
                    <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and
                    <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.
                </div>
            </form>
        </section>
    </main>
@endsection

@push('scripts')
    {{-- 第三方資源：動態效果與彈出式對話視窗套件 --}}
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- 安全寫法：動態由配置設定檔讀取驗證金鑰，加載 Google reCAPTCHA V3 服務腳本 --}}
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

    {{-- 聯絡我們頁面專屬前端邏輯控制腳本 --}}
    <script src="{{ asset('js/frontend/contact.js') }}"></script>
@endpush
