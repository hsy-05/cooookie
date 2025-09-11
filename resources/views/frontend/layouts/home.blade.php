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

    {{-- 餅乾特色區塊 --}}
    <section class="cookie-feature-section">
        <div class="container text-center">
            <h2 class="section-title" data-aos="fade-up">餅乾的特色</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                來自職人手作的堅持，每一口都值得細細品味。
            </p>

            <div class="row feature-row justify-content-center">
                {{-- Feature 1 --}}
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('images/feature_1.png') }}" alt="嚴選食材">
                        </div>
                        <h4 class="feature-title">嚴選食材</h4>
                        <p class="feature-desc">
                            每一樣原料皆由我們親自挑選，確保品質與風味的極致表現。
                        </p>
                    </div>
                </div>

                {{-- Feature 2 --}}
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('images/feature_2.png') }}" alt="手工烘焙">
                        </div>
                        <h4 class="feature-title">手工烘焙</h4>
                        <p class="feature-desc">
                            堅持小批量手工製作，烘焙出香氣四溢、酥脆綿密的口感。
                        </p>
                    </div>
                </div>

                {{-- Feature 3 --}}
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <img src="{{ asset('images/feature_3.png') }}" alt="送禮首選">
                        </div>
                        <h4 class="feature-title">送禮首選</h4>
                        <p class="feature-desc">
                            高質感的包裝與口感，讓每一份餅乾都是最溫暖的心意。
                        </p>
                    </div>
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
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
        });
    </script>
@endpush
