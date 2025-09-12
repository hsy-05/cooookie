@extends('frontend.layouts.app') {{-- 共用 layout --}}

@section('title', '首頁')

@section('content')
    {{-- Banner 區塊 --}}
    <div class="banner-wrapper">
        <div id="bannerCarousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @foreach ($banners as $key => $banner)
                    <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                        <a href="{{ $banner->adv_link_url ?? '#' }}">
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_url) }}" class="d-none d-md-block"
                                alt="banner-{{ $key }}">
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_m_url) }}" class="d-block d-md-none"
                                alt="banner-m-{{ $key }}">
                        </a>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#bannerCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </a>
            <a class="carousel-control-next" href="#bannerCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </a>
        </div>
    </div>

    {{-- 餅乾特色區塊（新版，左右圖文交錯） --}}
    <section class="cookie-features-section">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">餅乾特色</h2>
                <p class="section-subtitle">每一塊餅乾，都是我們對品質與用心的堅持</p>
            </div>

            {{-- 特徵一：天然食材 --}}
            <div class="row feature-block align-items-center mb-5" data-aos="fade-up">
                <div class="col-md-6 feature-img">
                    <img src="{{ asset('images/feature1.jpg') }}" alt="天然食材" class="img-fluid rounded shadow" />
                </div>
                <div class="col-md-6 feature-text pl-md-5">
                    <h3 class="feature-title">天然食材</h3>
                    <p class="feature-desc">嚴選頂級小麥與新鮮奶油，堅持不用人工添加物，讓每一口都是純粹天然的好味道。</p>
                </div>
            </div>

            {{-- 特徵二：手工製作（圖片反轉） --}}
            <div class="row feature-block align-items-center mb-5 flex-md-row-reverse" data-aos="fade-up">
                <div class="col-md-6 feature-img">
                    <img src="{{ asset('images/feature2.jpg') }}" alt="手工製作" class="img-fluid rounded shadow" />
                </div>
                <div class="col-md-6 feature-text pr-md-5">
                    <h3 class="feature-title">手工製作</h3>
                    <p class="feature-desc">傳承老師傅的工法與細膩手藝，讓餅乾不只是零食，而是帶著溫度與靈魂的作品。</p>
                </div>
            </div>

            {{-- 特徵三：多樣口味 --}}
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

{{-- AOS 動畫初始化 --}}
@push('scripts')
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-out-cubic',
            once: true,
        });
    </script>
@endpush
