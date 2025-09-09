@extends('frontend.layouts.app') {{-- 假設有共用 layout --}}

@section('title', '首頁')

@section('content')
    <div class="banner-wrapper">
        <div id="bannerCarousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @foreach ($banners as $key => $banner)
                    <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                        <a href="{{ $banner->adv_link_url ?? '#' }}">
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_url) }}" class="d-none d-md-block"
                                alt="banner-{{ $key }}">
                            <img src="{{ asset($UPLOAD_PATH . '/' . $banner->adv_img_m_url) }}" class="d-block d-md-none"
                                alt="banner-m-{{ $key }}">
                        </a>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#bannerCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </a>
            <a class="carousel-control-next" href="#bannerCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </a>
        </div>
    </div>
@endsection
