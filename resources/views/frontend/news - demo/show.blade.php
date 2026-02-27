@extends('frontend.layouts.app')

@section('title', '春季限定：櫻花鹽漬奶油餅乾｜最新消息')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- ▬▬▬ 1. 橫幅 ▬▬▬ --}}
    <section class="page-banner">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1622170456996-eb5bdf4eb5e8?q=80&w=1920" alt="News Banner"
                class="banner-img js-parallax-img">
        </div>
        <div class="banner-txt">
            <h1 class="banner-title js-fade-up">最新消息</h1>
            <p class="banner-subtitle js-fade-up">LATEST NEWS</p>
        </div>
    </section>


    <div class="container-1600">
        {{-- 面包屑 --}}
        @include('components.frontend.breadcrumb')

        {{-- ▬▬▬ 3. 文章內容 ▬▬▬ --}}
        <article class="article-section">
            <div class="container article-container">

                <header class="article-header js-fade-up">
                    <div class="article-meta">
                        <span class="meta-cat">新品上市</span>
                        <span class="meta-date">2025.03.15</span>
                    </div>
                    <h1 class="article-title">春季限定：櫻花鹽漬奶油餅乾，浪漫登場</h1>
                </header>

                {{-- Summernote 內容 --}}
                <div class="editor-content js-fade-up">
                    <p>當時序進入三月，微風中開始帶有淡淡的花香...</p>
                    <p>我們嚴選日本神奈川縣的八重櫻，搭配法國伊思尼奶油...</p>

                    <img src="https://images.unsplash.com/photo-1525151497928-85aa9c792131?q=80&w=1000" alt="櫻花餅乾製作過程">

                    <h3>職人手作的堅持</h3>
                    <p>每一朵櫻花都由師傅親手挑選、清洗...</p>

                    <ul>
                        <li><strong>食材：</strong>日本八重櫻、伊思尼奶油、鑽石麵粉。</li>
                        <li><strong>保存：</strong>常溫 21 天。</li>
                        <li><strong>販售期間：</strong>即日起至 4/30。</li>
                    </ul>
                </div>

                {{-- 上下篇 --}}
                <div class="article-nav js-fade-up">
                    <div class="nav-item prev">
                        <span class="nav-label">PREVIOUS</span>
                        <a href="#">台北信義新天地快閃店</a>
                    </div>
                    <div class="nav-item next">
                        <span class="nav-label">NEXT</span>
                        <a href="#">無其他文章</a>
                    </div>
                </div>

                <a href="{{ url('/news') }}" class="back-list-btn js-fade-up">返回列表</a>
            </div>
        </article>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            gsap.registerPlugin(ScrollTrigger);

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

            gsap.utils.toArray('.js-fade-up').forEach(el => {
                gsap.fromTo(el, {
                    y: 30,
                    opacity: 0
                }, {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
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
