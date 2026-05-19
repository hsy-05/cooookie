/**
 * 產品模組核心邏輯
 * 包含：列表篩選、Swiper 相簿聯動、LightGallery 燈箱
 */
const ProductModule = (() => {

    /**
     * 初始化列表頁篩選與搜尋
     */
    const initListFeatures = () => {
        const filterBtns = document.querySelectorAll('[data-category]');
        const productItems = document.querySelectorAll('.p-product-item');
        const searchInput = document.querySelector('[data-product-search]');

        if (!productItems.length) return;

        // 統一更新顯示邏輯
        const updateDisplay = () => {
            const activeCat = document.querySelector('.p-product-filter__button.is-active').dataset.category;
            const keyword = searchInput?.value.toLowerCase() || '';

            productItems.forEach(item => {
                const itemCat = item.dataset.category;
                const itemTitle = item.dataset.title;
                const catMatch = activeCat === '全部商品' || itemCat === activeCat;
                const searchMatch = itemTitle.includes(keyword);

                item.style.display = (catMatch && searchMatch) ? 'block' : 'none';
            });
        };

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                updateDisplay();
            });
        });

        searchInput?.addEventListener('input', updateDisplay);
    };

    /**
     * 初始化內頁 Swiper 與 LightGallery
     */
    const initGallery = () => {
        const thumbSlider = new Swiper('.js-slider-thumbs', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
        });

        const mainSlider = new Swiper('.js-slider-main', {
            spaceBetween: 10,
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            thumbs: { swiper: thumbSlider },
        });

        // 初始化 LightGallery 燈箱
        const galleryEl = document.getElementById('lightgallery');
        if (galleryEl) {
            lightGallery(galleryEl, {
                plugins: [lgFullscreen],
                speed: 500,
                selector: '.swiper-slide', // 指向 Swiper 的 slide
                download: false
            });
        }
    };

    return {
        init: () => {
            initListFeatures();
            initGallery();
        }
    };
})();

document.addEventListener('DOMContentLoaded', ProductModule.init);
