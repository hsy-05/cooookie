@extends('frontend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- 頁面橫幅標題區 --}}
    <section class="page-banner">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1622170456996-eb5bdf4eb5e8?q=80&w=1920&auto=format&fit=crop" alt="News Banner" class="banner-img js-parallax-img">
        </div>
        <div class="banner-txt">
            <h2 class="banner-title js-fade-up">最新消息</h2>
            <span class="banner-subtitle js-fade-up">LATEST NEWS</span>
        </div>
    </section>

    <div class="container-1600">
        {{-- 麵包屑 --}}
        @include('components.frontend.breadcrumb')

        <article class="article-section">
            <div class="container">

                <header class="article-header js-fade-up">
                    {{-- 移除 inline style，使用 detail-meta-wrapper 類名控制 --}}
                    <div class="detail-meta-wrapper">
                        <time class="n-date">{{ $news->created_at->format('Y / m / d') }}</time>
                        @if ($news->category)
                            <span class="n-cat-label">{{ $news->category->descs->firstWhere('lang_id', $langId)->name ?? '' }}</span>
                        @endif
                    </div>
                    <div class="article-main-title">{{ $desc->title ?? '無標題' }}</div>
                </header>

                <div class="article-content-wrapper js-fade-up">
                    <div class="editor-content">
                        {!! $desc->content ?? '內容編輯中...' !!}
                    </div>
                </div>

                {{-- 文章導覽：整合 SEO 與結構化排版 --}}
                <nav class="article-footer-nav js-fade-up">

                    {{-- 上一則：若無資料則渲染佔位 div 以維持佈局 --}}
                    @if ($prevNews)
                        @php $prevTitle = $prevNews->descs->firstWhere('lang_id', $langId)->title ?? ''; @endphp
                        <a href="{{ route('news.show', ['news' => $prevNews->news_id]) }}" class="nav-btn prev"
                            title="上一篇：{{ $prevTitle }}">
                            <span class="arrow"><i class="fas fa-chevron-left"></i></span>
                            <span class="nav-title text-limit-1">PREV：{{ $prevTitle }}</span>
                        </a>
                    @else
                        <div class="nav-btn disabled"></div>
                    @endif

                    {{-- 返回列表：純文字設計感按鈕 --}}
                    <a href="{{ session('last_news_list_url', route('news.index')) }}" class="back-to-list">
                        返回列表
                    </a>

                    {{-- 下一則：若無資料則渲染佔位 div 以維持佈局 --}}
                    @if ($nextNews)
                        @php $nextTitle = $nextNews->descs->firstWhere('lang_id', $langId)->title ?? ''; @endphp
                        <a href="{{ route('news.show', ['news' => $nextNews->news_id]) }}" class="nav-btn next"
                            title="下一篇：{{ $nextTitle }}">
                            <span class="nav-title text-limit-1">NEXT：{{ $nextTitle }}</span>
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
    <script>
        /**
         * 內頁內容強化處理
         * 處理 Summernote 輸出的圖片與非預期標籤
         */
        (function() {
            const initArticleContent = () => {
                const editorContent = document.querySelector('.editor-content');
                if (!editorContent) return;

                const imgs = editorContent.querySelectorAll('img');
                imgs.forEach(img => {
                    // 專業做法：不直接寫 style，若需處理則設為屬性或類名
                    img.loading = 'lazy';
                    if (img.getAttribute('style')) {
                        img.removeAttribute('style');
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', initArticleContent);
        })();
    </script>
@endpush
