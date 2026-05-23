@extends('frontend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- 頁面橫幅標題區 --}}
    {{-- 建議 Banner 圖片尺寸：1920px * 450px --}}
    <section class="page-banner">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1622170456996-eb5bdf4eb5e8?q=80&w=1920&auto=format&fit=crop" alt="News Banner" class="banner-img js-parallax-img">
        </div>
        <div class="c-banner-txt">
            <h2 class="c-banner-title js-fade-up">最新消息</h2>
            <span class="c-banner-subtitle js-fade-up">LATEST NEWS</span>
        </div>
    </section>

    <div class="container-1600">
        {{-- 麵包屑組件 --}}
        @include('components.frontend.breadcrumb')

        {{-- 文章主結構 --}}
        <article class="article-section">
            <div class="article-container">

                {{-- 文章標頭區 --}}
                <header class="article-header js-fade-up">
                    <div class="detail-meta-wrapper">
                        <time class="n-date">{{ $news->created_at->format('Y / m / d') }}</time>
                        @if ($news->category)
                            <span class="n-cat-label">{{ $news->category->descs->firstWhere('lang_id', $langId)->name ?? '' }}</span>
                        @endif
                    </div>
                    <h1 class="article-main-title">{{ $desc->title ?? '無標題' }}</h1>
                </header>

                {{-- 文章主要內文區（承接 Summernote 編輯器） --}}
                <div class="article-content-wrapper js-fade-up">
                    <div class="g__edit-wrap">
                        {!! $desc->content ?? '內容編輯中...' !!}
                    </div>
                </div>

                {{-- 文章底部導覽：上一頁、返回列表、下一頁 --}}
                <nav class="article-footer-nav js-fade-up">

                    @if ($prevNews)
                        <a href="{{ route('news.show', ['news' => $prevNews->news_id]) }}" class="nav-btn prev" title="上一篇：{{ $prevNews->descs->firstWhere('lang_id', $langId)->title ?? '' }}">
                            <span class="arrow"><i class="fas fa-chevron-left"></i></span>
                            <span class="nav-title text-limit-1">PREV：{{ $prevNews->descs->firstWhere('lang_id', $langId)->title ?? '' }}</span>
                        </a>
                    @else
                        <div class="nav-btn disabled"></div>
                    @endif

                    <div class="back-box">
                        <a href="{{ session('last_news_list_url', route('news.index')) }}" class="back-to-list">
                            返回列表
                        </a>
                    </div>

                    @if ($nextNews)
                        <a href="{{ route('news.show', ['news' => $nextNews->news_id]) }}" class="nav-btn next" title="下一篇：{{ $nextNews->descs->firstWhere('lang_id', $langId)->title ?? '' }}">
                            <span class="nav-title text-limit-1">NEXT：{{ $nextNews->descs->firstWhere('lang_id', $langId)->title ?? '' }}</span>
                            <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    @else
                        <div class="nav-btn disabled"></div>
                    @endif

                </nav>

            </div>
        </article>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/frontend/news.js') }}"></script>
@endpush
