@extends('frontend.layouts.app')

@section('title', '首頁')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush


@php
    /**
     * 模擬後台傳入的最新消息資料
     * 用途：在 Controller 尚未串接前，確保前端版面開發正常
     * 格式：使用 stdClass 模擬資料庫 Eloquent Model 物件
     */

    $categories = [
        ['title' => '經典原味', 'subtitle' => 'Classic Series', 'img' => 'https://images.unsplash.com/photo-1590080876102-9473629d7e23?q=80&w=600&auto=format&fit=crop'],
        ['title' => '濃厚黑巧', 'subtitle' => 'Chocolate Series', 'img' => 'https://images.unsplash.com/photo-1618923850107-d1a234d7a73a?q=80&w=600&auto=format&fit=crop'],
        ['title' => '靜岡抹茶', 'subtitle' => 'Tea Series', 'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=600&auto=format&fit=crop'],
        ['title' => '季節限定', 'subtitle' => 'Seasonal Limited', 'img' => 'https://images.unsplash.com/photo-1519340333755-56e9c1d04579?q=80&w=600&auto=format&fit=crop'],
        ['title' => '婚禮喜餅', 'subtitle' => 'Wedding Gift', 'img' => 'https://images.unsplash.com/photo-1605256485303-345383569777?q=80&w=600&auto=format&fit=crop'],
    ];
    $newsList = collect([
        (object)[
            'id' => 1,
            'title' => '2026 春季限定：焦糖抹茶職人手工餅乾上市',
            'summary' => '我們結合了日本宇治抹茶與法式手工焦糖，打造出層次豐富的春季限定口味。每一口都能感受到職人的溫度與對食材的堅持...',
            'image' => 'news/spring-special.jpg', // 實際路徑請確認 storage 是否有此圖
            'created_at' => now()->subDays(2)
        ],
        (object)[
            'id' => 2,
            'title' => '品牌官網正式上線，會員招募活動同步開跑',
            'summary' => '為了提供更優質的購物體驗，我們的全新官網正式啟動！現在加入會員即可享有首購 9 折優惠，並獲得專屬入會禮一份...',
            'image' => 'news/website-launch.jpg',
            'created_at' => now()->subDays(5)
        ],
        (object)[
            'id' => 3,
            'title' => '台北旗艦店：手作工坊體驗課程預約中',
            'summary' => '想親自體驗職人的製作工藝嗎？台北旗艦店現正開放週末手作課程預約，由首席烘焙師親自指導，帶您深入了解每一塊甜點背後的秘密...',
            'image' => 'news/workshop.jpg',
            'created_at' => now()->subDays(10)
        ],
        (object)[
            'id' => 4,
            'title' => '原料溯源：與在地農場的契作故事',
            'summary' => '我們深信優質的原料是甜點的靈魂。從雞蛋、麵粉到乳製品，我們堅持與台灣在地友善農場合作，確保每一份產品都安心可靠...',
            'image' => 'news/source.jpg',
            'created_at' => now()->subMonths(1)
        ]
    ]);
@endphp

@section('content')

    {{-- 1. Hero Section: 輪播 + 視差文字 --}}
    <section class="hero-slider" aria-label="首頁焦點輪播">
        {{-- js-hero-swiper 僅作為 JS 鉤子，樣式交給 CSS --}}
        <div class="swiper js-hero-swiper" style="height: 100%;">
            <div class="swiper-wrapper">
                @forelse ($banners as $banner)
                    <div class="swiper-slide">
                        <a href="{{ $banner->adv_link_url ?? '#' }}" class="d-block w-100 h-100 position-relative"
                            title="查看更多詳細內容">
                            {{-- 使用 picture 標籤也是一種 SEO 優化的專業寫法，這裡維持 img 寫法符合您的結構 --}}
                            <img src="{{ asset('storage/' . $banner->adv_img_url) }}"
                                alt="{{ $banner->adv_title ?? 'banner' }}" class="banner-bg-img d-none d-md-block" />

                            <img src="{{ asset('storage/' . ($banner->adv_img_m_url ?? $banner->adv_img_url)) }}"
                                alt="{{ $banner->adv_title ?? 'banner' }}" class="banner-bg-img d-block d-md-none" />
                        </a>

                        <div class="banner-content-overlay">
                            <div class="banner-text-container">
                                <h2 class="banner-main-title">COOOOKIE</h2>
                                <p class="banner-sub-title">每一口，都是幸福的滋味</p>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- 防呆邏輯：當後台沒抓到資料時顯示預設內容 --}}
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
                @endforelse
            </div>

            {{-- 分頁器主體 --}}
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
    <section class="section-intro" aria-labelledby="intro-title">
        <div class="intro-container">
            <div class="intro-grid">

                {{-- 左側：橫向長方形圖片 (3:2) - 768px 以下隱藏 --}}
                <div class="intro-img-wrap js-reveal-img">
                    <img src="https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?q=80&w=1500&auto=format&fit=crop"
                        alt="精選天然食材製作工藝" class="intro-img js-parallax-img" loading="lazy">
                </div>

                {{-- 右側：品牌論述區 --}}
                <article class="intro-text">
                    <header>
                        <span class="section-tag js-fade-up">Brand Essence</span>
                        <h2 id="intro-title" class="section-title js-fade-up">
                            純粹，<br>所以不簡單
                        </h2>
                    </header>

                    <div class="deco-line js-line-grow"></div>

                    <p class="section-desc js-fade-up">
                        我們相信，美味的餅乾不需要複雜的添加物。嚴選法國發酵奶油、日本鑽石麵粉，以及台灣在地的新鮮土雞蛋。
                        每一個步驟都堅持手工製作，從麵團的攪拌到烘烤的火候，我們近乎偏執地追求完美，只為了帶給您最純粹的幸福滋味。
                    </p>

                    <footer>
                        <a href="{{ url('/about') }}" class="btn-read-more js-fade-up">
                            VIEW MORE
                        </a>
                    </footer>
                </article>

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
    {{-- 漂浮裝飾元素 --}}
    <div class="deco-shape deco-shape--prod-1 js-float-anim"></div>
    <div class="deco-shape deco-shape--prod-2 js-rotate-anim"></div>

    <div class="container">
        <div class="section-header">
            <span class="section-tag js-fade-up">EXPLORE COLLECTIONS</span>
            <h2 class="section-title js-fade-up">產品系列</h2>
        </div>

        {{-- 輪播外層包覆：確保導覽按鈕定位正確 --}}
        <div class="category-swiper-wrapper js-fade-up">

            <div class="swiper js-category-swiper category-swiper">
                <div class="swiper-wrapper">
                    {{--
                        實作提醒：正式環境請將此 @foreach 與後台資料對接
                    --}}
                    @foreach ($categories as $cat)
                        <div class="swiper-slide">
                            <a href="{{ url('/products') }}" class="cat-card">
                                <article class="cat-img-box">
                                    <img src="{{ $cat['img'] }}" alt="{{ $cat['title'] }}" class="cat-img" loading="lazy">

                                    {{-- 產品遮罩：預設隱藏次要資訊，Hover 才展開 --}}
                                    <div class="cat-overlay">
                                        <div class="cat-glass-box">
                                            <div class="cat-info-content">
                                                <h3 class="cat-title">{{ $cat['title'] }}</h3>
                                                <p class="cat-subtitle">{{ $cat['subtitle'] }}</p>
                                            </div>
                                            <span class="cat-more">VIEW MORE</span>
                                        </div>
                                    </div>
                                </article>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 導覽按鈕：精確定位於左右最邊緣中央 --}}
            <div class="swiper-btn-prev swiper-aw js-cat-prev"><i></i></div>
            <div class="swiper-btn-next swiper-aw js-cat-next"><i></i></div>

        </div>
    </div>
</section>

{{-- 6. Latest News: 最新消息 (精緻優化版) --}}
<section class="section-news" aria-labelledby="news-heading">
    <div class="container">
        {{-- 區塊標題區 --}}
        <header class="section-header">
            <span class="section-tag js-fade-up">LATEST NEWS</span>
            <h2 id="news-heading" class="section-title js-fade-up">最新消息</h2>
        </header>

        {{-- 輪播容器：增加 swiper-initialized 控制初始顯示 --}}
        <div class="swiper js-news-swiper news-swiper js-fade-up">
            <div class="swiper-wrapper">
                @for ($i = 1; $i <= 6; $i++)
                    <div class="swiper-slide">
                        <article>
                            <a href="{{ url('/news/detail') }}" class="news-card">
                                {{-- 圖片區域：增加精緻標籤 --}}
                                <div class="news-img-box">
                                    <img src="https://images.unsplash.com/photo-1574376874341-f979491b6a83?q=80&w=600&auto=format&fit=crop"
                                         alt="最新消息圖示 {{ $i }}" class="news-img" loading="lazy">

                                    {{-- 流行小設計：懸浮日期標籤 --}}
                                    <div class="news-date-badge">
                                        <time datetime="2025-03-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                            <span class="day">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="month">MAR</span>
                                        </time>
                                    </div>

                                    {{-- 遮罩裝飾 --}}
                                    <div class="news-img-overlay"></div>
                                </div>

                                {{-- 內文區域 --}}
                                <div class="news-content">
                                    <div class="news-category">季節限定</div>
                                    <h3 class="news-title">春季限定櫻花餅乾禮盒 {{ $i }}</h3>
                                    <p class="news-excerpt">嚴選日本進口鹽漬櫻花，搭配師傅手作酥脆餅皮，每一口都能感受到濃厚的春天氣息...</p>

                                    <div class="news-footer">
                                        <span class="news-btn-text">READ MORE</span>
                                        <span class="news-arrow-icon"></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                @endfor
            </div>
        </div>

        {{-- 輪播導覽按鈕 --}}
        <div class="control-btn js-fade-up">
            <div class="swiper-btn-prev swiper-aw js-news-prev"><i></i></div>
            <div class="swiper-btn-next swiper-aw js-news-next"><i></i></div>
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
/**
 * 圖片加載管理系統
 * 用途：監測圖片下載狀態，完成後顯示圖片並停止骨架屏動畫
 */
const handleImagePreload = () => {
    const images = document.querySelectorAll('.cat-img, .news-img');

    images.forEach(img => {
        // 如果圖片已經在快取中加載好了
        if (img.complete) {
            img.classList.add('is-loaded');
            img.parentElement.style.animation = 'none'; // 停止動畫
        } else {
            // 監聽加載完成事件
            img.addEventListener('load', function() {
                img.classList.add('is-loaded');
                img.parentElement.style.animation = 'none'; // 停止動畫
            });

            // 如果加載失敗
            img.addEventListener('error', function() {
                img.parentElement.style.background = '#e0e0e0';
                img.parentElement.style.animation = 'none';
                img.style.display = 'none';
            });
        }
    });
};

document.addEventListener("DOMContentLoaded", function() {

    const settings = {
        fadeDistance: 60,
        animDuration: 1.2,
        parallaxPower: 15,
        easeType: "power2.out"
    };

    /**
     * 首頁大橫幅 (Hero Swiper)
     */
    const initHeroBanner = () => {
        const heroEl = document.querySelector('.js-hero-swiper');
        if (!heroEl) return;

        new Swiper(heroEl, {
            loop: true,
            speed: 1200,
            autoplay: { delay: 6000, disableOnInteraction: false },
            effect: 'fade',
            fadeEffect: { crossFade: true },
            pagination: { el: '.swiper-pagination', clickable: true },
            on: {
                init: function() {
                    const activeSlide = this.slides[this.activeIndex];
                    gsap.to(activeSlide.querySelectorAll('.banner-main-title, .banner-sub-title'), {
                        y: 0, opacity: 1, duration: 1, stagger: 0.2, ease: settings.easeType
                    });
                },
                slideChangeTransitionStart: function() {
                    let activeSlide = this.slides[this.activeIndex];
                    gsap.fromTo(activeSlide.querySelectorAll('.banner-main-title, .banner-sub-title'),
                    { y: settings.fadeDistance, opacity: 0 },
                    { y: 0, opacity: 1, duration: 1, stagger: 0.2, ease: settings.easeType });

                    gsap.fromTo(activeSlide.querySelector('.banner-bg-img'), { scale: 1.1 },
                    { scale: 1, duration: 8, ease: "power1.out" });
                }
            }
        });
    };

    /**
     * 全域捲動淡入
     */
    const initScrollAnims = () => {
        gsap.utils.toArray('.js-fade-up').forEach(item => {
            gsap.from(item, {
                y: settings.fadeDistance,
                opacity: 0,
                duration: settings.animDuration,
                ease: settings.easeType,
                scrollTrigger: {
                    trigger: item,
                    start: 'top 88%',
                    toggleActions: 'play none none reverse'
                }
            });
        });
    };

    /**
     * 圖片視差與裝飾線動態
     */
    const initVisualDepth = () => {
        if (window.innerWidth <= 768) return;
        gsap.utils.toArray('.js-parallax-img').forEach(wrap => {
            const img = wrap.querySelector('img');
            if (!img) return;
            gsap.to(img, {
                yPercent: settings.parallaxPower,
                ease: 'none',
                scrollTrigger: { trigger: wrap, start: 'top bottom', end: 'bottom top', scrub: 1 }
            });
        });

        gsap.utils.toArray('.js-line-grow').forEach(line => {
            gsap.from(line, {
                width: 0, duration: 1.5, ease: "expo.out",
                scrollTrigger: { trigger: line, start: 'top 90%' }
            });
        });
    };

    /**
     * 萬用輪播初始化器
     */
    const initSwiper = (selector, nav, showCount = 3) => {
        const target = document.querySelector(selector);
        if (!target) return;

        return new Swiper(target, {
            slidesPerView: 1,
            spaceBetween: 24,
            loop: true,
            speed: 900,
            grabCursor: true,
            watchSlidesProgress: true,
            navigation: { nextEl: nav.next, prevEl: nav.prev },
            breakpoints: {
                480: { slidesPerView: 1.2, spaceBetween: 16 },
                768: { slidesPerView: 2, spaceBetween: 24 },
                1024: { slidesPerView: showCount, spaceBetween: 32 }
            },
            on: {
                init: function() {
                    // 初始化完成後，讓容器顯示
                    target.classList.add('swiper-initialized');
                    // 再次觸發圖片檢查，確保圖片狀態正確
                    handleImagePreload();
                }
            }
        });
    };

    const initProcessSteps = () => {
        const triggerEl = document.querySelector('.process-steps');
        if (!triggerEl) return;
        ScrollTrigger.create({
            trigger: triggerEl,
            start: 'top 75%',
            onEnter: () => {
                const lineFill = document.querySelector('.js-line-fill');
                if (lineFill) lineFill.style.width = '100%';
                gsap.to('.js-step-anim', { opacity: 1, y: 0, duration: 0.8, stagger: 0.2, ease: "back.out(1.7)" });
            },
            once: true
        });
    };

    const initDecoLoops = () => {
        gsap.utils.toArray('.js-float-anim').forEach((el, i) => {
            gsap.to(el, { y: 30, duration: 5 + i, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        });
        gsap.utils.toArray('.js-rotate-anim').forEach((el, i) => {
            gsap.to(el, { rotation: 360, duration: 20 + i * 5, repeat: -1, ease: 'linear' });
        });
    };

    // 啟動圖片預加載監控
    handleImagePreload();

    try {
        initHeroBanner();
        initScrollAnims();
        initVisualDepth();
        initDecoLoops();
        initProcessSteps();
        initSwiper('.js-category-swiper', { next: '.js-cat-next', prev: '.js-cat-prev' }, 3);
        initSwiper('.js-news-swiper', { next: '.js-news-next', prev: '.js-news-prev' }, 3);
    } catch (error) {
        console.error("專業提醒：動畫初始化發生錯誤:", error);
    }
});
</script>
@endpush
