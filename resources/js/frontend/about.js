/**
 * 【COOOOKIE】關於我們頁面動畫
 * 負責：Banner 進場、核心價值 Pin 滾動、靈魂食材交互。
 */

document.addEventListener("DOMContentLoaded", () => {

    // 防呆：如果沒載入 GSAP 就跳出
    if (typeof gsap === 'undefined') return;

    // 參數設定
    const PAGE_CONFIG = {
        scrollSpeed: 1000,     // 影響 Pin 區域滾動的時間感
        headerOffset: "30px"   // 避開 Header 的頂部偏移
    };

    /**
     * Banner 橫幅與視差
     */
    const initHeroBanner = () => {
        // 文字進場
        gsap.set(".js-hero-text", { autoAlpha: 0, y: 30 });
        gsap.to(".js-hero-text", { autoAlpha: 1, y: 0, duration: 1.5, ease: "power2.out", delay: 0.5 });

        // 背景旋轉裝飾
        gsap.to(".js-rotate-scroll", {
            rotation: 360, ease: "none",
            scrollTrigger: { trigger: "body", start: "top top", end: "bottom bottom", scrub: 2 }
        });
    };

    /**
     * 核心價值輪播動畫 (固定在畫面中切換)
     */
    const initValuesSection = () => {
        const items = gsap.utils.toArray(".js-v-item");
        const pics = gsap.utils.toArray(".v-pic");
        if (!items.length) return;

        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: ".js-values-pin",
                start: `top ${PAGE_CONFIG.headerOffset}`,
                end: `+=${PAGE_CONFIG.scrollSpeed}`,
                pin: true,
                scrub: 0.5,
                invalidateOnRefresh: true
            }
        });

        items.forEach((item, i) => {
            const bar = item.querySelector('.v-bar');
            const title = item.querySelector('.v-head');

            // 處理進場邏輯
            if (i === 0) {
                gsap.set(item, { opacity: 1, y: "0%" });
                gsap.set(bar, { width: "100%" });
                gsap.set(title, { color: "#8c6a4b" });
            } else {
                tl.to(item, { opacity: 1, y: "0%", duration: 1 })
                  .to(bar, { width: "100%", duration: 0.8 }, "<")
                  .to(title, { color: "#8c6a4b", duration: 0.8 }, "<")
                  .to(pics[i], { clipPath: "inset(0% 0% 0% 0%)", duration: 1 }, "<");
            }

            // 處理離場邏輯 (除了最後一個)
            if (i < items.length - 1) {
                tl.to({}, { duration: 0.5 }) // 增加停留感
                  .to(item, { opacity: 0, y: "-20%", duration: 1 })
                  .to(bar, { width: "0%", duration: 0.5 }, "<");
            }
        });
    };

    /**
     * 靈魂食材動畫卡片
     */
    const initIngredientAnim = () => {
        gsap.utils.toArray('.js-ing-anim').forEach((el) => {
            const img = el.querySelector('img');
            const text = el.querySelector('.ing-text');
            const overlay = el.querySelector('.ing-overlay');

            const tl = gsap.timeline({
                scrollTrigger: {
                    trigger: el,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                }
            });

            tl.fromTo(el, { scale: 0.9, opacity: 0 }, { scale: 1, opacity: 1, duration: 0.8, ease: "back.out(1.2)" })
              .fromTo(img, { scale: 1.2 }, { scale: 1, duration: 1.2, ease: "power2.out" }, "<")
              .fromTo(text, { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8 }, "-=0.6")
              .add(() => {
                  if (overlay) overlay.classList.add('is-visible');
              });
        });
    };

    /**
     * 對角線視差與 Resize 處理
     */
    const initUtils = () => {
        gsap.fromTo(".js-diag-parallax",
            { backgroundPosition: "0% 0%" },
            {
                backgroundPosition: "100% 100%", ease: "none",
                scrollTrigger: { trigger: ".js-diag-parallax", start: "top bottom", end: "bottom top", scrub: true }
            }
        );

        window.addEventListener("resize", () => ScrollTrigger.refresh());
    };

    // 依序執行
    initHeroBanner();
    initValuesSection();
    initIngredientAnim();
    initUtils();
});
