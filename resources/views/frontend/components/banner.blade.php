{{-- resources/views/frontend/components/banner.blade.php --}}
{{-- 使用 Swiper.js 實作，支援 lazy load、loop、autoplay、桌機/手機圖片切換與蓋板設計 --}}

@php
    // 若變數沒被傳，避免 blade error，改用空集合
    $adverts = $adverts ?? collect();
@endphp

@if($adverts->count())
  <section class="banner-section" aria-label="網站橫幅">
    <div class="swiper-container banner-swiper">
      <div class="swiper-wrapper">
        @foreach($adverts as $ad)
          @php
            // 路徑：請確保 adv_img_url/adv_img_m_url 已為 storage 或 public 可用 URL
            $imgDesktop = $ad->adv_img_url ?? $ad->adv_img_m_url;
            $imgMobile  = $ad->adv_img_m_url ?? $ad->adv_img_url;
            $alt = e($ad->adv_name ?? '網站橫幅');
            // 若你有 webp 版本也可以在 source 補上 webp srcset
            $link = $ad->adv_link_url ?? '#';
          @endphp

          <div class="swiper-slide">
            {{-- 先用 picture 保證響應式且可用 webp 之類的 source --}}
            <a href="{{ $link }}" class="banner-link" target="{{ $link !== '#' ? '_blank' : '_self' }}" rel="noopener">
              <picture class="banner-picture">
                {{-- 可擴充：若有 webp 可加 <source type="image/webp"> --}}
                <source media="(min-width: 769px)" srcset="{{ asset($imgDesktop) }}">
                <img
                  class="banner-img swiper-lazy"
                  data-src="{{ asset($imgMobile) }}"
                  alt="{{ $alt }}"
                  loading="lazy"
                  width="1920"
                  height="600"
                />
              </picture>
              {{-- Swiper lazy preloader --}}
              <div class="swiper-lazy-preloader"></div>

              {{-- 覆蓋蓋板（漸層 + 文字） --}}
              <div class="banner-overlay">
                <div class="banner-inner container">
                  {{-- 範例文字/按鈕（如不需要可移除） --}}
                  <h2 class="banner-title">{{ $ad->adv_name ?? '' }}</h2>
                  @if(!empty($ad->adv_subname))
                    <p class="banner-sub">{{ $ad->adv_subname }}</p>
                  @endif
                  @if(!empty($ad->adv_link_url))
                    <div class="banner-cta">
                      <span class="btn btn-cta">了解更多</span>
                    </div>
                  @endif
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>

      {{-- 自訂導航按鈕（把預設箭頭換成較有設計感） --}}
      <div class="swiper-button-prev" aria-label="上一張"></div>
      <div class="swiper-button-next" aria-label="下一張"></div>

      {{-- 指示點：可自動產生 --}}
      <div class="swiper-pagination" aria-hidden="true"></div>
    </div>
  </section>

  {{-- 初始化 Swiper，放入 stack scripts 供 layout 引用 --}}
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // 初始化 banner Swiper，只要頁面有 .banner-swiper 就會啟用
      const bannerEl = document.querySelector('.banner-swiper');
      if (!bannerEl) return;

      const swiper = new Swiper(bannerEl, {
        loop: true,
        speed: 700,               // 切換速度（毫秒）
        effect: 'slide',         // 'fade' 亦可，slide 在有圖片時表現更平滑
        autoplay: {
          delay: 3800,
          disableOnInteraction: false,
        },
        keyboard: { enabled: true },
        grabCursor: true,
        preloadImages: false,    // 關閉預載，改用 lazy
        lazy: {
          loadPrevNext: true,    // 載入前後張，避免翻頁時空白
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
        // accessibility
        a11y: {
          prevSlideMessage: '上一張',
          nextSlideMessage: '下一張',
        },
        // 當滑鼠進入則暫停自動播放，離開復原
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
