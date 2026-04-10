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
 * 【無用】初始化 Swiper 輪播
 *
 * 具備：
 * - 自動隱藏控制項（資料太少時不顯示分頁與前後按鈕）
 * - RWD 斷點（手機 → 平板 → 桌機）
 * - loop + fraction 分頁顯示正常，不會出現 05 / 07 這種亂增筆數
 * - 5 筆資料也能正常循環，不會卡在 03 / 05
 *
 * @param {string} selector Swiper 容器選擇器，例如 '.js-product-cat-swiper'
 * @param {object} navConfig 控制元件選擇器物件
 * @param {string} navConfig.pagination 分頁容器選擇器，例如 '.js-product-cat-pagination'
 * @param {string} navConfig.prev 上一張按鈕選擇器，例如 '.js-product-cat-prev'
 * @param {string} navConfig.next 下一張按鈕選擇器，例如 '.js-product-cat-next'
 * @param {number} desktopCount 桌機版 slidesPerView 數量（預設 3）
 */
const initSwiper = (selector, navConfig, desktopCount = 3) => {
    const el = document.querySelector(selector);
    if (!el) return null;

    // 取得資料總張數（原始資料張數，不是 loop 後的複製張數）
    const slides = el.querySelectorAll('.swiper-slide');
    const total = slides.length;

    // 取得控制元件
    const paginationEl = document.querySelector(navConfig.pagination);
    const prevBtn = document.querySelector(navConfig.prev);
    const nextBtn = document.querySelector(navConfig.next);

    /**
     * 檢查是否需要顯示控制項（前後按鈕、分頁）
     * 只有資料比目前顯示張數多時才顯示
     *
     * @param {number} currentSlidesPerView 當前斷點的 slidesPerView
     * @returns {boolean} 是否啟用控制
     */
    const checkVisibility = (currentSlidesPerView) => {
        if (total <= currentSlidesPerView) {
            if (paginationEl) paginationEl.style.display = 'none';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            return false;
        } else {
            if (paginationEl) paginationEl.style.display = 'flex';
            if (prevBtn) prevBtn.style.display = 'block';
            if (nextBtn) nextBtn.style.display = 'block';
            return true;
        }
    };

    // 初始化 Swiper
    const swiper = new Swiper(selector, {
        // 防止小數位誤差導致左右切卡
        roundLengths: true,
        spaceBetween: 0,
        slidesPerView: 1,

        // 只有資料 >=2 筆才開啟 loop，少於 2 筆就當純直向輪播
        loop: total > 1,

        // 取消「強制整張複製」的 loopedSlides，讓 Swiper 用預設邏輯
        // 這樣分頁顯示才不會變成 05 / 07、03 / 06 這種亂增數字
        // 如果你真的碰到 5 筆卡住，再用 `loopAdditionalSlides` 微調，而不是直接設 loopedSlides = total
        // loopedSlides: total,  // 這行會讓分頁 total 跳亂，所以先拿掉

        navigation: {
            nextEl: navConfig.next,
            prevEl: navConfig.prev,
        },

        pagination: {
            el: navConfig.pagination,
            type: 'fraction',

            formatFractionCurrent:  function (number) {
                return ('0' + number).slice(-2);
            },
            formatFractionTotal: function (number) {
                return ('0' + number).slice(-2);
            },
            renderFraction: function (currentClass, totalClass, index) {
                return `<span class="${currentClass}"></span>` +
                            `<span class="gap"></span>` +
                            `<span class="${totalClass}"></span>`;
            }
        },

        // 響應式：支援 320px 到 4K，不同斷點顯示不同張數
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 20 },
            1024: { slidesPerView: desktopCount, spaceBetween: 30 },
            1440: { slidesPerView: desktopCount, spaceBetween: 40 }
        },

        on: {
            // Swiper 初始化完成時，檢查目前顯示張數，決定是否顯示控制項
            init: function () {
                checkVisibility(this.params.slidesPerView);
            },

            // 當螢幕尺寸變動導致斷點切換時，重新檢查顯示張數和控制項
            breakpoint: function () {
                checkVisibility(this.params.slidesPerView);
            }
        }
    });

    return swiper;
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
    } catch (error) {
        console.error("專業提醒：動畫初始化發生錯誤:", error);
    }
});
