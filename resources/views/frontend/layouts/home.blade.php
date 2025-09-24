@extends('frontend.layouts.app')

@section('title', '首頁')

@section('content')
    {{-- Banner 區塊 --}}
    <div class="banner-wrapper">
        <div class="interactive-circles"></div> <!-- 互動圓形容器 -->
        <div class="swiper banner-swiper">
            <div class="swiper-wrapper">
                @foreach ($banners as $banner)
                    <div class="swiper-slide">
                        <a href="{{ $banner->adv_link_url ?? '#' }}">
                            {{-- 電腦版圖片，md 以上顯示 --}}
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_url) }}" alt="banner"
                                class="banner-bg-img d-none d-md-block" />
                            {{-- 手機版圖片，md 以下顯示，若無手機版圖片則 fallback 電腦版 --}}
                            <img src="{{ asset($UPLOAD_PATH . '/' . ($banner->adv_img_m_url ?? $banner->adv_img_url)) }}"
                                alt="banner-mobile" class="banner-bg-img d-block d-md-none" />
                        </a>
                        <div class="banner-content-overlay">
                            <div class="banner-text-container">
                                <h1 class="banner-main-title" data-aos="fade-up" data-aos-delay="200">COOOOKIE</h1>
                                <p class="banner-sub-title" data-aos="fade-up" data-aos-delay="400">每一口，都是幸福的滋味</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- 分頁器 -->
            <div class="swiper-pagination"></div>
        </div>

        <div class="scrolldown">SCROLL</div>
    </div>

    {{-- 餅乾特色區塊（新版，卡片式設計與互動動畫） --}}
    <section class="cookie-features-section py-5 py-md-7"> {{-- 調整 padding 間距 --}}
        <div class="container">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">餅乾特色</h2>
                <p class="section-subtitle">每一塊餅乾，都是我們對品質與用心的堅持</p>
            </div>
            <div class="features-grid">
                @foreach ($features as $feature)
                    <div class="feature-card" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        {{-- 增加 AOS 延遲，製造錯落感 --}}
                        <div class="feature-card-inner"> {{-- 新增內層用於餅乾外框效果 --}}
                            <div class="feature-img-wrapper">
                                {{-- 圖片尺寸建議 450x350px --}}
                                <img src="{{ asset($UPLOAD_PATH . '/' . $feature->adv_img_url) }}"
                                    alt="{{ $feature->title }}" class="feature-img" />
                                <div class="cookie-border-deco"></div> {{-- 餅乾外框裝飾 --}}
                            </div>
                            <div class="feature-content">
                                <h3 class="feature-title">{{ $feature->title }}</h3>
                                <p class="feature-desc">{{ $feature->description ?? '每一塊餅乾，都是我們對品質與用心的堅持。' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="i-tasting">
        <div class="i-tasting__overlay"></div> <!-- 新增遮罩層 -->
        <div class="i-tasting__content">
            <div class="container-1440">
                <div class="i-tasting__icon"></div>
                <h2 class="g__box-ti">
                    <span class="en">TASTE TESTING</span>
                    <span class="tw">試吃申請</span>
                </h2>
                <p class="i-tasting__text">
                    我們有多樣化的點心、中式與西式的喜餅、活動餐盒、手工特色麵包，<br>
                    可以滿足您的味蕾，尋找美味的甜點，歡迎申請試吃。
                </p>
                <div class="g-btn-wrap">
                    <a href="{{ url('/contact') }}" title="按我申請" class="g-btn-link">按我申請</a>
                </div>
            </div>
        </div>
        <div class="i-tasting__bg"><img src="{{ asset('images/taste-pic4.jpg') }}" alt="(圖)*"></div>
    </section>


@endsection

{{-- AOS 動畫初始化 --}}
@push('scripts')
    <script>
        AOS.init({
            duration: 1000, // 動畫持續時間
            easing: 'ease-out-cubic', // 動畫緩動函數
            once: true, // 動畫只執行一次
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.banner-swiper', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 800,
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const bannerWrapper = document.querySelector('.banner-wrapper');
            const circlesContainer = document.querySelector('.interactive-circles');

            if (!bannerWrapper || !circlesContainer) return;

            bannerWrapper.addEventListener('mousemove', function(e) {
                const rect = bannerWrapper.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                // 建立圓形元素
                const circle = document.createElement('div');
                circle.classList.add('circle');

                // 隨機大小 30~60 px
                const size = Math.random() * 30 + 30;
                circle.style.width = size + 'px';
                circle.style.height = size + 'px';

                // 位置設定在滑鼠附近，隨機偏移 -20~20 px
                const offsetX = (Math.random() - 0.5) * 40;
                const offsetY = (Math.random() - 0.5) * 40;
                circle.style.left = (x + offsetX) + 'px';
                circle.style.top = (y + offsetY) + 'px';

                circlesContainer.appendChild(circle);

                // 動畫結束後移除元素，避免 DOM 過多
                circle.addEventListener('animationend', () => {
                    circle.remove();
                });
            });
        });
    </script>
@endpush
