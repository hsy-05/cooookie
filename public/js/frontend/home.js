/**
 * 圖片加載管理系統
 * 用途：監測圖片下載狀態，完成後顯示圖片並停止骨架屏動畫
 */
const handleImagePreload = () => {
    const images = document.querySelectorAll('.product-cat-img, .news-img');

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
        initVisualDepth();
        initDecoLoops();
        initProcessSteps();
        initSwiper('.js-product-cat-swiper', { next: '.js-product-cat-next', prev: '.js-product-cat-prev' }, 3);
        initSwiper('.js-news-swiper', { next: '.js-news-next', prev: '.js-news-prev' }, 3);
    } catch (error) {
        console.error("專業提醒：動畫初始化發生錯誤:", error);
    }
});
