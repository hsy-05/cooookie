@extends('frontend.layouts.app')

@section('title', '最新消息｜COOOOKIE')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- ▬▬▬ 1. 頁面橫幅（含視差） ▬▬▬ --}}
    <section class="page-banner">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1622170456996-eb5bdf4eb5e8?q=80&w=1920&auto=format&fit=crop"
                alt="Latest News Banner" class="banner-img js-parallax-img">
        </div>

        <div class="banner-txt">
            <h1 class="banner-title js-fade-up">最新消息</h1>
            <p class="banner-subtitle js-fade-up" data-delay="0.1">LATEST NEWS</p>
        </div>
    </section>


    <div class="container-1600">
    {{-- 面包屑 --}}
    @include('components.frontend.breadcrumb')

        {{-- ▬▬▬ 3. 消息列表 ▬▬▬ --}}
        <section class="section-news-list">
            <div class="container">

                {{-- ▬ 分類篩選 ▬ --}}
                <div class="news-filter js-fade-up" data-delay="0.2">
                    <a href="#" class="filter-btn active">全部消息</a>
                    <a href="#" class="filter-btn">新品上市</a>
                    <a href="#" class="filter-btn">活動快訊</a>
                    <a href="#" class="filter-btn">媒體報導</a>
                </div>

                {{-- ▬ 模擬後台資料 ▬ --}}
                @php
                    $news_list = [
                        [
                            'id' => 1,
                            'cat' => '新品上市',
                            'date' => '2025.03.15',
                            'title' => '春季限定：櫻花鹽漬奶油餅乾，浪漫登場',
                            'desc' => '嚴選日本進口八重櫻，搭配法國伊思尼發酵奶油，鹹甜交織的口感。',
                            'img' => 'https://images.unsplash.com/photo-1622467827417-bbe2237067a9?q=80&w=800',
                        ],
                        [
                            'id' => 2,
                            'cat' => '活動快訊',
                            'date' => '2025.02.20',
                            'title' => '台北信義新天地 A11 快閃店，限時三週',
                            'desc' => '我們來到台北了！現場提供現烤試吃，快閃限定口味同步登場。',
                            'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=800',
                        ],
                        [
                            'id' => 3,
                            'cat' => '媒體報導',
                            'date' => '2025.01.10',
                            'title' => '榮獲 2024 年度最佳伴手禮推薦 Top 10',
                            'desc' => '感謝支持，這份榮耀屬於每一位喜愛 COOOOKIE 的你們。',
                            'img' => 'https://images.unsplash.com/photo-1740742765403-bd3fe7739637?q=80&w=800',
                        ],
                    ];
                @endphp

                <div class="news-grid">
                    @foreach ($news_list as $index => $item)
                        <article class="news-card js-fade-up" data-delay="{{ ($index % 3) * 0.1 }}">
                            <a href="{{ url('/news/detail') }}">
                                <div class="n-img-box">
                                    <span class="n-cat">{{ $item['cat'] }}</span>
                                    <img src="{{ $item['img'] }}" alt="{{ $item['title'] }}" class="n-img">
                                </div>

                                <div class="n-info">
                                    <span class="n-date">{{ $item['date'] }}</span>
                                    <h2 class="n-title">{{ $item['title'] }}</h2>
                                    <p class="n-desc">{{ $item['desc'] }}</p>
                                    <span class="n-more">
                                        Read More
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                {{-- ▬ 分頁 ▬ --}}
                <div class="pagination-wrap js-fade-up">
                    <a href="#" class="page-btn">&larr;</a>
                    <a href="#" class="page-btn active">1</a>
                    <a href="#" class="page-btn">2</a>
                    <a href="#" class="page-btn">3</a>
                    <a href="#" class="page-btn">&rarr;</a>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.registerPlugin(ScrollTrigger);

            // 視差
            gsap.to(".js-parallax-img", {
                yPercent: 20,
                ease: "none",
                scrollTrigger: {
                    trigger: ".page-banner",
                    start: "top top",
                    end: "bottom top",
                    scrub: true
                }
            });

            // Scroll Reveal
            gsap.utils.toArray('.js-fade-up').forEach(el => {
                gsap.fromTo(el, {
                    y: 50,
                    opacity: 0
                }, {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
                    delay: el.dataset.delay || 0,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: el,
                        start: "top 85%"
                    }
                });
            });
        });
    </script>
@endpush
