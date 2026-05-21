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
     * 初始化 GSAP 插件與 Lenis 同步
     */
    if (typeof gsap !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

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

        // 將 Lenis 滾動事件同步到 ScrollTrigger，這能解決座標偵測不準的問題
        lenis.on('scroll', ScrollTrigger.update);

        const raf = (time) => {
            lenis.raf(time);
            requestAnimationFrame(raf);
        };
        requestAnimationFrame(raf);

        return lenis;
    };

    const lenis = initLenis();

    // 常用 DOM 元素選取
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
     * 回頂端進度環初始化
     */
    let circumference = 0;
    if (dom.ringCircle) {
        const radius = dom.ringCircle.r.baseVal.value;
        circumference = 2 * Math.PI * radius; // 圓周長公式 2πr
        dom.ringCircle.style.strokeDasharray = `${circumference} ${circumference}`;
        dom.ringCircle.style.strokeDashoffset = circumference;
    }

    /**
     * 捲動監聽 UI 變化
     */
    if (lenis) {
        lenis.on('scroll', (e) => {
            const scrollTop = e.animatedScroll;
            const docHeight = document.body.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;

            // 頂部導覽列：切換深淺色狀態
            if (dom.header) {
                dom.header.classList.toggle('scrolled', scrollTop > SITE_CONFIG.headerTrigger);
            }
            // 回頂端按鈕：控制顯現與消失
            if (dom.backToTop) {
                dom.backToTop.classList.toggle('is-visible', scrollTop > SITE_CONFIG.backToTopTrigger);
            }

            // 回頂端圓環：根據捲動比例填滿路徑
            if (dom.ringCircle) {
                const offset = circumference - (scrollPercent / 100) * circumference;
                dom.ringCircle.style.strokeDashoffset = offset;
            }
        });
    }

    /**
     * 行動版側邊選單控制
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

        // 手機板點擊事件
        dom.hamburger.addEventListener('click', () => {
            toggleMenu(!dom.mainNav.classList.contains('is-open'));
        });

        // 選單內部鏈接點擊後自動關閉（用於單頁捲動或行動版體驗）
        dom.navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= SITE_CONFIG.mobileBreakpoint) toggleMenu(false);
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
     * 回頂端按鈕點擊
     */
    if (dom.backToTop && lenis) {
        dom.backToTop.addEventListener('click', () => {
            lenis.scrollTo(0, { lerp: 0.05 }); // 微調回彈速度，讓捲動更優雅
        });
    }

    /**
     * 全站通用視差效果
     */
    const initParallax = () => {
        if (typeof gsap === 'undefined') return;

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
    };

    /**
     * 初始化通用上浮動畫 (Fade Up)
     * 加入了高度校正與座標刷新防呆。
     */
    const initFadeUp = () => {
        if (typeof gsap === 'undefined') return;

        const fadeElements = gsap.utils.toArray('.js-fade-up');

        // 先清理舊有的 ScrollTrigger 避免重複計算高度
        ScrollTrigger.getAll().forEach(st => {
            if (st.vars.trigger && st.vars.trigger.classList && st.vars.trigger.classList.contains('js-fade-up')) {
                st.kill();
            }
        });

        fadeElements.forEach(el => {
            gsap.fromTo(el,
                { autoAlpha: 0, y: 30 },
                {
                    autoAlpha: 1,
                    y: 0,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: el,
                        start: "top 90%",
                        toggleActions: "play none none reverse",
                        markers: true // 完成除錯後改為 false
                    }
                }
            );
        });

        /**
         * 強制高度刷新邏輯：
         * 使用延遲刷新確保 Swiper 與圖片撐開高度後才鎖定偵測點。
         */
        setTimeout(() => {
            ScrollTrigger.refresh();
        }, 100);
    };

    /**
     * 專業啟動流程：
     * 1. 執行視差 -> 2. 執行上浮 -> 3. 延遲刷新座標
     */
    initParallax();
    initFadeUp();
    initAsideNavTitle();

    // 最終防呆：當頁面完全穩定後再次校正座標，解決頁尾多餘空白問題
    setTimeout(() => {
        ScrollTrigger.refresh();
    }, 500);
});

/**
     * ==========================================
     * ✨ 分類選單手機版優化 (已修正作用域問題)
     * ==========================================
     */
    function initAsideNavTitle() {
        // 抓取當前處於啟動狀態 (.current) 的 li 裡面的文字
        let currentText = $('#js-aside-nav-list').find('li.current span').text().trim();

        // 防呆機制：如果找不到任何選中的分類，就給予預設文字
        if (currentText === '') {
            currentText = '選擇分類';
        }

        // 將文字寫入手機版的提示大按鈕中
        $('#js-aside-nav-current-text').text(currentText);
    }

    // 點擊手機版選單大按鈕時，控制選單展開或收合
    $('#js-aside-nav-btn').on('click', function(event) {
        event.stopPropagation();
        $(this).toggleClass('is-on');
        $('#js-aside-nav-list').slideToggle(300);
    });

    // 點擊網頁的其他任意空白處，自動收合選單
    $(document).on('click', function() {
        if ($(window).width() <= 991) {
            if ($('#js-aside-nav-list').is(':visible')) {
                $('#js-aside-nav-btn').removeClass('is-on');
                $('#js-aside-nav-list').slideUp(300);
            }
        }
    });
