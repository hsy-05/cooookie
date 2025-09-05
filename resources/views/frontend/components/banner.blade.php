{{-- 可重用的 Banner 區塊：接受 $adverts collection --}}
@if(!empty($adverts) && $adverts->count())
  <div id="siteCarousel" class="carousel slide mb-4" data-ride="carousel">
    <ol class="carousel-indicators">
      @foreach($adverts as $i => $ad)
        <li data-target="#siteCarousel" data-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}"></li>
      @endforeach
    </ol>

    <div class="carousel-inner">
      @foreach($adverts as $i => $ad)
        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
          {{-- 優先顯示電腦圖 adv_img_url，若沒有就用手機圖 --}}
          @php $img = $ad->adv_img_url ?? $ad->adv_img_m_url; @endphp
          @if($ad->adv_link_url)
            <a href="{{ $ad->adv_link_url }}" target="_blank" rel="noopener">
              <img src="{{ asset($img) }}" class="d-block w-100" alt="{{ $ad->adv_name ?? '' }}">
            </a>
          @else
            <img src="{{ asset($img) }}" class="d-block w-100" alt="{{ $ad->adv_name ?? '' }}">
          @endif
        </div>
      @endforeach
    </div>

    <a class="carousel-control-prev" href="#siteCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">上一則</span>
    </a>
    <a class="carousel-control-next" href="#siteCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">下一則</span>
    </a>
  </div>
@endif
