@extends('frontend.layouts.app')

@section('title', '首頁')

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
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- 上一頁/下一頁客製按鈕 --}}
            <div class="carousel-aw">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- 這裡放首頁本體內容, 比如餅乾特色等 --}}
    <section class="cookie-features-section">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">餅乾特色</h2>
                <p class="section-subtitle">每一塊餅乾，都是我們對品質與用心的堅持</p>
            </div>
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
            </div>
        </div>
    </section>
@endsection

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
        });
    });
</script>
