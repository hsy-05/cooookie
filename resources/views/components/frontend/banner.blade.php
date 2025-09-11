@php
    // 傳入廣告資料，避免未定義錯誤
    $adverts = $adverts ?? collect();
@endphp

@if($adverts->count())
<section class="banner-section" aria-label="首頁橫幅輪播">
    <div class="swiper-container banner-swiper">
        <div class="swiper-wrapper">
            @foreach($adverts as $ad)
                @php
                    $imgDesktop = $ad->adv_img_url ?? $ad->adv_img_m_url;
                    $imgMobile  = $ad->adv_img_m_url ?? $ad->adv_img_url;
                    $alt = e($ad->adv_name ?? '網站橫幅');
                    $link = $ad->adv_link_url ?? null;
                    $hasLink = $link && $link !== '#';
                @endphp

                <div class="swiper-slide">
                    {{-- 使用 picture 支援響應式圖片 --}}
                    @if($hasLink)
                    <a href="{{ $link }}" class="banner-link" target="_blank" rel="noopener">
                    @endif

                        <picture class="banner-picture">
                            <source media="(min-width: 769px)" srcset="{{ asset($imgDesktop) }}">
                            <img
                                class="banner-img swiper-lazy"
                                data-src="{{ asset($imgMobile) }}"
                                alt="{{ $alt }}"
                                loading="lazy"
                                width="1920"
                                height="800"
                            />
                        </picture>

                        <div class="swiper-lazy-preloader"></div>

                        {{-- 蓋板：加上漸層與文字 --}}
                        <div class="banner-overlay">
                            <div class="banner-inner container">
                                <div class="banner-content" data-aos="fade-up" data-aos-delay="200">
                                    <h2 class="banner-title">{{ $ad->adv_name ?? '' }}</h2>
                                    @if(!empty($ad->adv_subname))
                                        <p class="banner-sub">{{ $ad->adv_subname }}</p>
                                    @endif
                                    @if($hasLink)
                                        <div class="banner-cta mt-3">
                                            <span class="btn btn-cta">了解更多</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @if($hasLink)
                    </a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Swiper 自訂箭頭 --}}
        <div class="swiper-button-prev" aria-label="上一張"></div>
        <div class="swiper-button-next" aria-label="下一張"></div>

        {{-- 分頁指示點 --}}
        <div class="swiper-pagination" aria-hidden="true"></div>
    </div>
</section>

{{-- 初始化 Swiper --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bannerEl = document.querySelector('.banner-swiper');
        if (!bannerEl) return;

        new Swiper(bannerEl, {
            loop: true,
            speed: 800,
            effect: 'fade',
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            keyboard: { enabled: true },
            grabCursor: true,
            preloadImages: false,
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 1,
            },
            slidesPerView: 1,
            centeredSlides: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            a11y: {
                prevSlideMessage: '上一張',
                nextSlideMessage: '下一張',
            },
            on: {
                init: function() {
                    const s = this;
                    bannerEl.addEventListener('mouseenter', () => s.autoplay && s.autoplay.stop());
                    bannerEl.addEventListener('mouseleave', () => s.autoplay && s.autoplay.start());
                }
            }
        });
    });
</script>
@endpush
@endif
