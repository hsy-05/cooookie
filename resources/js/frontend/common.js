/**
 * 【COOOOKIE】全站通用交互核心
 * 負責項目：Lenis 平滑滾動、Header 縮放監聽、行動版選單切換、回頂端進度條、全站視差優化。
 */

// 網站全域設定：修改這裡即可調整全站手感，無需更動下方邏輯
const SITE_CONFIG = {
    headerTrigger: 60,         // 捲動超過此數值 (px) Header 會變色/縮小
    backToTopTrigger: 400,     // 捲動超過此數值顯示回頂端按鈕
    mobileBreakpoint: 1024,    // 行動版選單生效的斷點
    scrollDuration: 1.4,       // 平滑滾動的持續時間 (秒)
    parallaxStrength: 15       // 視差移動的強度 (yPercent)
};

window.addEventListener('load', () => {

    /**
     * 初始化 Lenis 平滑滾動
     * 解決原生滾動生硬問題，並為 GSAP 動畫提供基準。
     * @returns {Object|null} Lenis 實例
     */
    const initLenis = () => {
        if (typeof Lenis === 'undefined') {
            console.warn('Lenis 尚未載入，將使用原生滾動。');
            return null;
        }

        const lenis = new Lenis({
            duration: SITE_CONFIG.scrollDuration,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // 經典指數緩動
            smoothWheel: true,
            smoothTouch: false // 行動版通常維持原生手感以利操作
        });

        // 串接瀏覽器的渲染循環
        const raf = (time) => {
            lenis.raf(time);
            requestAnimationFrame(raf);
        };
        requestAnimationFrame(raf);

        return lenis;
    };

    const lenis = initLenis();

    // 啟動 GSAP 滾動插件
    if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // 統一選取常用的 DOM 元素，避免重複選取浪費效能
    const dom = {
        body: document.body,
        header: document.querySelector('.site-header'),
        hamburger: document.querySelector('.js-hamburger'),
        mainNav: document.querySelector('.js-main-nav'),
        navLinks: document.querySelectorAll('.nav-link'),
        backToTop: document.getElementById('backToTop'),
        ringCircle: document.querySelector('.progress-ring__circle')
    };

    /**
     * 初始化回頂端按鈕的圓環進度條
     * 利用 SVG stroke-dasharray 特性計算總長度。
     */
    let circumference = 0;
    if (dom.ringCircle) {
        const radius = dom.ringCircle.r.baseVal.value;
        circumference = 2 * Math.PI * radius; // 圓周長公式 2πr
        dom.ringCircle.style.strokeDasharray = `${circumference} ${circumference}`;
        dom.ringCircle.style.strokeDashoffset = circumference;
    }

    /**
     * 處理所有隨滾動產生的 UI 變化
     * 效能優化：將所有判斷寫在同一個滾動監聽內。
     */
    if (lenis) {
        lenis.on('scroll', (e) => {
            const scrollTop = e.animatedScroll;
            const docHeight = document.body.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;

            // 1. 頂部導覽列：切換深淺色狀態
            if (dom.header) {
                dom.header.classList.toggle('scrolled', scrollTop > SITE_CONFIG.headerTrigger);
            }

            // 2. 回頂端按鈕：控制顯現與消失
            if (dom.backToTop) {
                dom.backToTop.classList.toggle('is-visible', scrollTop > SITE_CONFIG.backToTopTrigger);
            }

            // 3. 回頂端圓環：根據捲動比例填滿路徑
            if (dom.ringCircle) {
                const offset = circumference - (scrollPercent / 100) * circumference;
                dom.ringCircle.style.strokeDashoffset = offset;
            }
        });
    }

    /**
     * 行動版側邊選單切換邏輯
     * @param {boolean} shouldOpen - 強制設定開啟或關閉
     */
    if (dom.hamburger && dom.mainNav) {
        const toggleMenu = (shouldOpen) => {
            dom.mainNav.classList.toggle('is-open', shouldOpen);
            dom.hamburger.classList.toggle('is-active', shouldOpen);

            // 邏輯分離：透過 Class 控制 Body 鎖定，不直接操作 Style
            dom.body.classList.toggle('is-locked', shouldOpen);

            // 質感優化：選單開啟時，讓文字鏈接依序浮現
            if (shouldOpen && typeof gsap !== 'undefined') {
                gsap.fromTo(dom.navLinks,
                    { y: 20, opacity: 0 },
                    { y: 0, opacity: 1, duration: 0.5, stagger: 0.1, ease: 'power2.out', delay: 0.2 }
                );
            }
        };

        // 漢堡鈕點擊事件
        dom.hamburger.addEventListener('click', () => {
            const isOpen = dom.mainNav.classList.contains('is-open');
            toggleMenu(!isOpen);
        });

        // 選單內部鏈接點擊後自動關閉（用於單頁捲動或行動版體驗）
        dom.navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= SITE_CONFIG.mobileBreakpoint) {
                    toggleMenu(false);
                }
            });
        });

        // 響應式防呆：當視窗從手機拉大至電腦版時，強制關閉手機版選單狀態
        window.addEventListener('resize', () => {
            if (window.innerWidth > SITE_CONFIG.mobileBreakpoint && dom.mainNav.classList.contains('is-open')) {
                toggleMenu(false);
            }
        });
    }

    /**
     * 點擊回頂端功能
     */
    if (dom.backToTop && lenis) {
        dom.backToTop.addEventListener('click', () => {
            lenis.scrollTo(0, { lerp: 0.05 }); // 微調回彈速度，讓捲動更優雅
        });
    }

    /**
     * 全站通用視差背景與圖層
     * 效能優化：使用 FastScrollEnd 並優化遍歷方式。
     */
    if (typeof gsap !== 'undefined') {
        const parallaxElements = gsap.utils.toArray('.js-parallax-img');

        parallaxElements.forEach(container => {
            const img = container.querySelector('img');
            if (!img) return;

            // 建立視差軌跡
            gsap.to(img, {
                yPercent: SITE_CONFIG.parallaxStrength,
                ease: 'none',
                scrollTrigger: {
                    trigger: container,
                    start: 'top bottom', // 當容器底部進入視窗時開始
                    end: 'bottom top',   // 當容器頂部離開視窗時結束
                    scrub: 1,            // 數值 1 增加平滑追隨感，比 true 更有質感
                    fastScrollEnd: true, // 防止快速捲動導致的運算負擔
                }
            });
        });
    }

    /**
     * 通用 Fade Up 效果
     */
    const initFadeUp = () => {
        gsap.utils.toArray('.js-fade-up').forEach(el => {
            gsap.fromTo(el, { autoAlpha: 0, y: 30 }, {
                autoAlpha: 1, y: 0, duration: 1, ease: "power2.out",
                scrollTrigger: { trigger: el, start: "top 90%", toggleActions: "play none none reverse" }
            });
        });
    };

    // 依序執行
    initFadeUp();
});
