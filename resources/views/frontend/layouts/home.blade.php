@extends('frontend.layouts.app')

@section('title', '首頁')

<<<<<<< HEAD
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
=======
{{-- 定義 banner 區塊 --}}

{{-- 定義 banner 區塊 改用 Swiper --}}
@section('banner')
    <div class="banner-swiper-wrapper">
        <!-- Swiper 容器 -->
        <div class="swiper banner-swiper">
            <div class="swiper-wrapper">
                @foreach ($banners as $key => $banner)
                    <div class="swiper-slide">
                        <a href="{{ $banner->adv_link_url ?? '#' }}">
                            <!-- 大螢幕用圖 -->
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_url) }}"
                                class="d-none d-md-block banner-slide-image" alt="banner-{{ $key }}">
                            <!-- 手機用圖 -->
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_m_url) }}"
                                class="d-block d-md-none banner-slide-image" alt="banner-m-{{ $key }}">
                        </a>
                        <div class="banner-overlay"></div>
                        <div class="banner-slide-text">
                            {{-- 如果你的 banner model 有標題或副標題，可以放這裡 --}}
                            {{-- 範例 --}}
                            @if (!empty($banner->title))
                                <h2>{{ $banner->title }}</h2>
                            @endif
                            @if (!empty($banner->subtitle))
                                <p>{{ $banner->subtitle }}</p>
                            @endif
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
                        </div>
                    </div>
                @endforeach
            </div>
<<<<<<< HEAD
            <!-- 分頁器 -->
            <div class="swiper-pagination"></div>
            <!-- 前後按鈕 -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
=======
            {{-- 上一頁/下一頁客製按鈕 --}}
            <div class="carousel-aw">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
        </div>

        <div class="scrolldown">SCROLL</div>
    </div>
@endsection

<<<<<<< HEAD


    {{-- 餅乾特色區塊（新版，卡片式設計與互動動畫） --}}
    <section class="cookie-features-section py-5 py-md-7"> {{-- 調整 padding 間距 --}}
=======
@section('content')
    {{-- 這裡放首頁本體內容, 比如餅乾特色等 --}}
    <section class="cookie-features-section">
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
        <div class="container">
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">餅乾特色</h2>
                <p class="section-subtitle">每一塊餅乾，都是我們對品質與用心的堅持</p>
            </div>
<<<<<<< HEAD
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
=======
            <div class="row feature-block align-items-center mb-5" data-aos="fade-up">
                <div class="col-md-6 feature-img">
                    <img src="{{ asset('images/feature1.jpg') }}" alt="天然食材" class="img-fluid rounded shadow" />
                </div>
                <div class="col-md-6 feature-text pl-md-5">
                    <h3 class="feature-title">天然食材</h3>
                    <p class="feature-desc">嚴選頂級小麥與新鮮奶油，堅持不用人工添加物，讓每一口都是純粹天然的好味道。</p>
                </div>
            </div>
            <div class="row feature-block align-items-center mb-5 flex-md-row-reverse" data-aos="fade-up">
                <div class="col-md-6 feature-img">
                    <img src="{{ asset('images/feature2.jpg') }}" alt="手工製作" class="img-fluid rounded shadow" />
                </div>
                <div class="col-md-6 feature-text pr-md-5">
                    <h3 class="feature-title">手工製作</h3>
                    <p class="feature-desc">傳承老師傅的工法與細膩手藝，讓餅乾不只是零食，而是帶著溫度與靈魂的作品。</p>
                </div>
            </div>
            <div class="row feature-block align-items-center mb-5" data-aos="fade-up">
                <div class="col-md-6 feature-img">
                    <img src="{{ asset('images/feature3.jpg') }}" alt="多樣口味" class="img-fluid rounded shadow" />
                </div>
                <div class="col-md-6 feature-text pl-md-5">
                    <h3 class="feature-title">多樣口味</h3>
                    <p class="feature-desc">經典奶油、抹茶、可可與限定創新口味，滿足每一種味蕾的期待。</p>
                </div>
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
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
                    <a href="tasting.php" title="按我申請" class="g-btn-link">按我申請</a>
                </div>
            </div>
        </div>
        <div class="i-tasting__bg"><img src="{{ asset('images/taste-pic4.jpg') }}" alt="(圖)*"></div>
    </section>


@endsection

<<<<<<< HEAD
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
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
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
=======
<!-- 引入 Swiper CSS & JS（可放在 layout 的 head / footer） -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bannerSwiper = new Swiper('.banner-swiper', {
            loop: true, // 迴圈播放
            effect: 'fade', // 淡入淡出效果
            speed: 1000, // 切換速度（1 秒）
            autoplay: {
                delay: 5000, // 每張圖顯示 5 秒
                disableOnInteraction: false, // 使用者點過後不停止自動播放
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.banner-swiper-pagination',
                clickable: true,
            },
            fadeEffect: {
                crossFade: true, // 淡入淡出時的交叉淡化效果
            },
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
        });
    });
</script>
