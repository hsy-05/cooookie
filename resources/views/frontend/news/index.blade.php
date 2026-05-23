@extends('frontend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- 頁面橫幅標題區 --}}
    {{-- 建議 Banner 圖片尺寸：1920px * 450px --}}
    <section class="page-banner news-page">
        <div class="banner-img-wrap">
            <img src="https://images.unsplash.com/photo-1622170456996-eb5bdf4eb5e8?q=80&w=1920" alt="News Banner"
                class="banner-img js-parallax-img">
        </div>
        <div class="c-banner-block">
            <span class="c-banner-subtitle">LATEST NEWS</span>
            <h2 class="c-banner-title">最新消息</h2>
        </div>
    </section>

    <div class="container-1600">
        {{-- 麵包屑 --}}
        @include('components.frontend.breadcrumb')

        <section class="section-news-list">
            <div class="container">

                {{-- 分類選單 --}}
                <nav class="aside-nav slide js-fade-up" data-delay="0.2">

                    {{-- 行動裝置專用下拉選單觸發鈕 --}}
                    <div class="aside-nav__btn" id="js-aside-nav-btn">
                        <span class="word" id="js-aside-nav-current-text">選擇分類</span>
                        <span class="icon"></span>
                    </div>

                    {{-- 分類連結清單 --}}
                    <ul class="aside-nav__tab flex reset" id="js-aside-nav-list">
                        <li class="{{ !$categoryId ? 'current' : '' }}">
                            <a href="{{ route('news.index') }}" title="全部消息">
                                <span>全部消息</span>
                            </a>
                        </li>

                        @foreach ($catList as $cat)
                            <li class="{{ $categoryId == $cat->cat_id ? 'current' : '' }}">
                                <a href="{{ route('news.category', $cat->cat_id) }}" title="{{ $cat->descs->first()->name ?? '未命名' }}">
                                    <span>{{ $cat->descs->first()->name ?? '未命名' }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- 消息網格 - 已整合手繪線條與職人背景 --}}
                <div class="news-grid">
                    @forelse ($newsList as $news)
                        <article class="news-card js-fade-up" data-delay="0.2">
                            <a href="{{ route('news.show', ['news' => $news->news_id]) }}"
                                title="{{ $news->currentDesc->title ?? '' }}">

                                {{-- 圖片與手繪遮罩複合區（建議上傳圖片尺寸：500px * 360px） --}}
                                <div class="n-img-box">
                                    <img src="{{ $news->image_url ? asset('storage/' . $news->image_url) : asset('images/default/defult-500X360.png') }}"
                                        alt="{{ $news->currentDesc->title ?? '' }}" class="n-img" loading="lazy">

                                    {{-- 自產品模組移植而來之精緻感手繪遮罩層 --}}
                                    <div class="p-product-item__overlay">
                                        <span class="view-text">VIEW MORE</span>
                                    </div>
                                </div>

                                {{-- 資訊文字區 --}}
                                <div class="n-info">
                                    <div class="n-meta">
                                        @if ($news->category)
                                            <span class="n-cat-label">{{ $news->category->currentDesc->name }}</span>
                                        @endif
                                        <time class="n-date" datetime="{{ $news->created_at->format('Y-m-d') }}">
                                            {{ $news->created_at->format('Y.m.d') }}
                                        </time>
                                    </div>
                                    <h2 class="n-title">{{ $news->currentDesc->title ?? 'Untitled' }}</h2>
                                    <p class="n-desc">{{ Str::limit(strip_tags($news->currentDesc->content ?? ''), 80) }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="no-data-wrapper">
                            <p>目前尚無相關消息</p>
                        </div>
                    @endforelse
                </div>

                {{-- 分頁器 --}}
                @if ($newsList->hasPages())
                    <nav class="pagination-wrap js-fade-up">
                        @if (!$newsList->onFirstPage())
                            <a href="{{ $newsList->previousPageUrl() }}" class="page-btn" rel="prev">&larr;</a>
                        @endif

                        @foreach ($newsList->getUrlRange(max(1, $newsList->currentPage() - 2), min($newsList->lastPage(), $newsList->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="page-btn {{ $page == $newsList->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach

                        @if ($newsList->hasMorePages())
                            <a href="{{ $newsList->nextPageUrl() }}" class="page-btn" rel="next">&rarr;</a>
                        @endif
                    </nav>
                @endif

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/frontend/news.js') }}"></script>
@endpush
