@extends('frontend.layouts.app')

@section('title', '首頁')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')

    {{-- 1. Hero Section: 輪播 + 視差文字 --}}
    <section class="hero-slider">
        <div class="swiper js-hero-swiper" style="height: 100%;">
            <div class="swiper-wrapper">
                {{-- 判斷是否有後台資料 --}}
                @if (isset($banners) && count($banners) > 0)
                    @foreach ($banners as $banner)
                        <div class="swiper-slide">
                            <a href="{{ $banner->adv_link_url ?? '#' }}" class="d-block w-100 h-100 position-relative">
                                {{-- 電腦版圖片 --}}
                                <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_url) }}" alt="banner"
                                    class="banner-bg-img d-none d-md-block" />
                                {{-- 手機版圖片 --}}
                                <img src="{{ asset($UPLOAD_PATH . '/' . ($banner->adv_img_m_url ?? $banner->adv_img_url)) }}"
                                    alt="banner-mobile" class="banner-bg-img d-block d-md-none" />
                            </a>
                            <div class="banner-content-overlay">
                                <div class="banner-text-container">
                                    <h2 class="banner-main-title">COOOOKIE</h2>
                                    <p class="banner-sub-title">每一口，都是幸福的滋味</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- 預設 Slide --}}
                    <div class="swiper-slide">
                        <div class="banner-bg-img"
                            style="background-image: url('https://images.unsplash.com/photo-1499636138143-bd630f5cf388?q=80&w=1920&auto=format&fit=crop'); background-size: cover; background-position: center;">
                        </div>
                        <div class="banner-content-overlay">
                            <div class="banner-text-container">
                                <h2 class="banner-main-title">HANDMADE</h2>
                                <p class="banner-sub-title">職人手作，溫暖人心</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    {{-- 2. Marquee: 動態跑馬燈 --}}
    <div class="marquee">
        <div class="marquee__inner">
            <span class="marquee__item">PREMIUM INGREDIENTS</span>
            <span class="marquee__item">HANDMADE WITH LOVE</span>
            <span class="marquee__item">NO ARTIFICIAL FLAVORS</span>
            <span class="marquee__item">FRESHLY BAKED</span>
            <span class="marquee__item">PREMIUM INGREDIENTS</span>
            <span class="marquee__item">HANDMADE WITH LOVE</span>
        </div>
    </div>

    {{-- 3. Intro Section: 品牌理念 --}}
    <section class="section-intro">
        {{-- 裝飾圖案 --}}
        <div class="deco-shape deco-shape--intro-1 js-float-anim"></div>
        <div class="deco-shape deco-shape--intro-2 js-rotate-anim"></div>

        <div class="container">
            <div class="intro-grid">
                <div class="intro-img-wrap js-parallax-img">
                    <img src="https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?q=80&w=1000&auto=format&fit=crop"
                        alt="Philosophy" class="intro-img">
                </div>
                <div class="intro-text">
                    <span class="section-tag js-fade-up">OUR PHILOSOPHY</span>
                    <h2 class="section-title js-fade-up">純粹，所以不簡單</h2>
                    <div class="deco-line js-line-grow"></div>
                    <p class="section-desc js-fade-up">
                        我們相信，美味的餅乾不需要複雜的添加物。嚴選法國發酵奶油、日本鑽石麵粉，以及台灣在地的新鮮土雞蛋。
                        每一個步驟都堅持手工製作，從麵團的攪拌到烘烤的火候，我們近乎偏執地追求完美。
                    </p>
                    <a href="{{ url('/about') }}" class="btn-read-more js-fade-up">閱讀更多</a>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Process Section: 製作工藝 --}}
    <section class="section-process">
        {{-- 裝飾圖案 --}}
        <div class="deco-shape deco-shape--process-1 js-rotate-anim"></div>
        <div class="deco-shape deco-shape--process-2 js-float-anim"></div>

        <div class="container">
            <div class="section-header">
                <span class="section-tag js-fade-up">HOW WE MAKE IT</span>
                <h2 class="section-title js-fade-up">職人手作工藝</h2>
            </div>

            <div class="process-steps">
                <div class="process-line"></div>
                <div class="process-line-fill js-line-fill"></div>

                <div class="process-step js-step-anim">
                    <div class="step-icon-box">1</div>
                    <h4 class="step-title">嚴選與過篩</h4>
                    <p class="step-desc">手工過篩麵粉，<br>確保空氣感。</p>
                </div>
                <div class="process-step js-step-anim" style="transition-delay: 0.2s;">
                    <div class="step-icon-box">2</div>
                    <h4 class="step-title">低溫發酵</h4>
                    <p class="step-desc">36小時熟成，<br>喚醒食材風味。</p>
                </div>
                <div class="process-step js-step-anim" style="transition-delay: 0.4s;">
                    <div class="step-icon-box">3</div>
                    <h4 class="step-title">精心烘烤</h4>
                    <p class="step-desc">精準控溫，<br>鎖住酥脆口感。</p>
                </div>
                <div class="process-step js-step-anim" style="transition-delay: 0.6s;">
                    <div class="step-icon-box">4</div>
                    <h4 class="step-title">完美包裝</h4>
                    <p class="step-desc">真空密封，<br>傳遞新鮮心意。</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Product Category : 產品系列 --}}
    <section class="section-categories">
        {{-- 裝飾元素 - 符合整體設計語系 --}}
        <div class="deco-shape deco-shape--prod-1 js-float-anim"></div>
        <div class="deco-shape deco-shape--prod-2 js-rotate-anim"></div>
        <div class="deco-shape deco-shape--prod-3 js-float-anim"></div>
        <div class="container">
            <div class="section-header">
                <span class="section-tag js-fade-up">EXPLORE COLLECTIONS</span>
                <h2 class="section-title js-fade-up">產品系列</h2>
                <p class="section-desc js-fade-up">探索我們為您精心準備的美味選擇，每一款都是經典。</p>
            </div>

            {{-- Category Swiper --}}
            <div class="swiper js-category-swiper category-swiper js-fade-up">
                <div class="swiper-wrapper">
                    {{-- 模擬後台資料 --}}
                    @php
                        $categories = [
                            ['title' => '經典原味', 'subtitle' => 'Classic Series', 'img' => 'https://images.unsplash.com/photo-1590080876102-9473629d7e23?q=80&w=600&auto=format&fit=crop'],
                            ['title' => '濃厚黑巧', 'subtitle' => 'Chocolate Series', 'img' => 'https://images.unsplash.com/photo-1618923850107-d1a234d7a73a?q=80&w=600&auto=format&fit=crop'],
                            ['title' => '靜岡抹茶', 'subtitle' => 'Tea Series', 'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=600&auto=format&fit=crop'],
                            ['title' => '季節限定', 'subtitle' => 'Seasonal Limited', 'img' => 'https://images.unsplash.com/photo-1519340333755-56e9c1d04579?q=80&w=600&auto=format&fit=crop'],
                            ['title' => '婚禮喜餅', 'subtitle' => 'Wedding Gift', 'img' => 'https://images.unsplash.com/photo-1605256485303-345383569777?q=80&w=600&auto=format&fit=crop'],
                        ];
                    @endphp

                    @foreach ($categories as $cat)
                        <div class="swiper-slide">
                            <a href="{{ url('/products') }}" class="cat-card">
                                <div class="cat-img-box">
                                    <img src="{{ $cat['img'] }}" alt="{{ $cat['title'] }}" class="cat-img">
                                </div>
                                <div class="cat-info">
                                    <h3 class="cat-title">{{ $cat['title'] }}</h3>
                                    <div class="cat-subtitle">{{ $cat['subtitle'] }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Navigation Arrows (Outside Container) --}}
            <div class="category-nav js-fade-up">
                <div class="swiper-btn-prev js-cat-prev"><i class="bi bi-arrow-left"></i> &larr;</div>
                <div class="swiper-btn-next js-cat-next">&rarr; <i class="bi bi-arrow-right"></i></div>
            </div>
        </div>
    </section>

    {{-- 6. Latest News: 最新消息 --}}
    <section class="section-news">
        {{-- 裝飾元素 - 符合整體設計語系 --}}
        <div class="deco-shape deco-shape--news-1 js-float-anim"></div>
        <div class="deco-shape deco-shape--news-2 js-rotate-anim"></div>
        <div class="deco-shape deco-shape--news-3 js-float-anim"></div>

        <div class="container">
            <div class="section-header">
                <span class="section-tag js-fade-up">LATEST NEWS</span>
                <h2 class="section-title js-fade-up">最新消息</h2>
                <div class="deco-line js-line-grow"></div>
            </div>

            <div class="swiper js-news-swiper news-swiper js-fade-up">
                <div class="swiper-wrapper">
                    {{-- 模擬後台新聞資料 --}}
                    @for ($i = 1; $i <= 6; $i++)
                        <div class="swiper-slide">
                            <a href="{{ url('/news/detail') }}" class="news-card">
                                <div class="news-img-box">
                                    <img src="https://images.unsplash.com/photo-1574376874341-f979491b6a83?q=80&w=600&auto=format&fit=crop" alt="News {{ $i }}" class="news-img">
                                    <div class="news-date">2025.03.{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</div>
                                </div>
                                <div class="news-content">
                                    <h3 class="news-title">春季限定櫻花餅乾禮盒 {{ $i }}，浪漫上市中</h3>
                                    <p class="news-excerpt">嚴選日本進口鹽漬櫻花，搭配法國發酵奶油，每一口都能感受到春天的氣息...</p>
                                    <span class="news-btn">
                                        READ MORE
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- News Navigation --}}
            <div class="news-nav js-fade-up">
                <div class="swiper-btn-prev js-news-prev">&larr;</div>
                <div class="swiper-btn-next js-news-next">&rarr;</div>
            </div>
        </div>
    </section>

    {{-- 7. Tasting Section: 試吃申請 --}}
    <section class="i-tasting-section">
        @include('components.frontend.i-tasting')
    </section>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. Hero Swiper (維持不變)
        const bannerSwiper = new Swiper('.js-hero-swiper', {
            loop: true, speed: 1200, autoplay: { delay: 6000, disableOnInteraction: false },
            effect: 'fade', fadeEffect: { crossFade: true },
            pagination: { el: '.swiper-pagination', clickable: true },
            on: {
                init: function() {
                    gsap.to(this.slides[this.activeIndex].querySelectorAll('.banner-main-title, .banner-sub-title'),
                        { y: 0, opacity: 1, duration: 1, stagger: 0.2, ease: "power2.out" });
                },
                slideChangeTransitionStart: function () {
                    let activeSlide = this.slides[this.activeIndex];
                    gsap.fromTo(activeSlide.querySelectorAll('.banner-main-title, .banner-sub-title'),
                        { y: 60, opacity: 0 }, { y: 0, opacity: 1, duration: 1, stagger: 0.2, ease: "power2.out" }
                    );
                    gsap.fromTo(activeSlide.querySelector('.banner-bg-img'),
                        { scale: 1.1 }, { scale: 1, duration: 8, ease: "power1.out" }
                    );
                }
            }
        });

        // 2. Product Category Swiper (New!)
        const categorySwiper = new Swiper('.js-category-swiper', {
            slidesPerView: 1.2,
            spaceBetween: 20,
            centeredSlides: false,
            grabCursor: true,
            navigation: {
                nextEl: '.js-cat-next',
                prevEl: '.js-cat-prev',
            },
            breakpoints: {
                640: { slidesPerView: 2.2, spaceBetween: 30 },
                1024: { slidesPerView: 3.2, spaceBetween: 40 }
            }
        });

        // 3. News Swiper (New!)
        const newsSwiper = new Swiper('.js-news-swiper', {
            slidesPerView: 1.1,
            spaceBetween: 20,
            grabCursor: true,
            navigation: {
                nextEl: '.js-news-next',
                prevEl: '.js-news-prev',
            },
            breakpoints: {
                640: { slidesPerView: 2.1, spaceBetween: 30 },
                1024: { slidesPerView: 3, spaceBetween: 40 }
            }
        });

        // 4. GSAP Fade Up & Animations
        gsap.utils.toArray('.js-fade-up').forEach(el => {
            gsap.fromTo(el, { y: 60, opacity: 0 }, {
                y: 0, opacity: 1, duration: 1, delay: el.dataset.delay || 0,
                scrollTrigger: { trigger: el, start: 'top 85%', toggleActions: 'play none none reverse' }
            });
        });

        gsap.utils.toArray('.js-parallax-img').forEach(wrap => {
            gsap.to(wrap.querySelector('img'), {
                yPercent: 20, ease: 'none',
                scrollTrigger: { trigger: wrap, start: 'top bottom', end: 'bottom top', scrub: true }
            });
        });

        gsap.utils.toArray('.js-line-grow').forEach(line => {
            gsap.fromTo(line, { width: 0 }, { width: '100px', duration: 1.5, ease: 'power2.out', scrollTrigger: { trigger: line, start: 'top 85%' } });
        });

        ScrollTrigger.create({
            trigger: '.process-steps', start: 'top 75%',
            onEnter: () => {
                document.querySelector('.js-line-fill').style.width = '100%';
                gsap.to('.js-step-anim', { opacity: 1, y: 0, duration: 0.8, stagger: 0.2, ease: "back.out(1.7)" });
            }
        });

        gsap.utils.toArray('.js-float-anim').forEach((shape, i) => {
            gsap.to(shape, { y: 30, duration: 6 + i, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        });
        gsap.utils.toArray('.js-rotate-anim').forEach((shape, i) => {
            gsap.to(shape, { rotation: 360, duration: 20 + i * 5, repeat: -1, ease: 'linear' });
        });

    });
</script>
@endpush
