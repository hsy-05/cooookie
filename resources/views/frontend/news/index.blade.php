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

        <section class="section-news-list">
            <div class="container">

                {{-- 分類選單 --}}
                <nav class="news-filter js-fade-up" data-delay="0.2">
                    <a href="{{ route('news.index') }}" class="filter-btn {{ !$categoryId ? 'active' : '' }}">全部消息</a>
                    @foreach ($catList as $cat)
                        <a href="{{ route('news.category', $cat->cat_id) }}"                             class="filter-btn {{ $categoryId == $cat->cat_id ? 'active' : '' }}">
                            {{ $cat->descs->first()->name ?? '未命名' }}
                        </a>
                    @endforeach
                </nav>

                {{-- 消息網格 --}}
                <div class="news-grid">
                    @forelse ($newsList as $item)
                        <article class="news-card js-fade-up" data-delay="0.2">
                            <a href="{{ route('news.show', ['news' => $item->news_id]) }}" title="{{ $item->desc->title ?? '' }}">
                                {{-- 圖片區 500*360 --}}
                                <div class="n-img-box">
                                    <img src="{{ $item->image_url ? asset('storage/' . $item->image_url) : asset('images/default-news.jpg') }}"
                                         alt="{{ $item->desc->title ?? '' }}"
                                         class="n-img"
                                         loading="lazy">
                                </div>

                                {{-- 資訊區 --}}
                                <div class="n-info">
                                    <div class="n-meta">
                                        @if ($item->category)
                                            <span class="n-cat-label">{{ $item->category->desc->name }}</span>
                                        @endif
                                        <time class="n-date" datetime="{{ $item->created_at->format('Y-m-d') }}">
                                            {{ $item->created_at->format('Y.m.d') }}
                                        </time>
                                    </div>
                                    <h2 class="n-title">{{ $item->desc->title ?? 'Untitled' }}</h2>
                                    <p class="n-desc">{{ Str::limit(strip_tags($item->desc->content ?? ''), 80) }}</p>
                                </div>
                            </a>
                        </article>
                    @empty
                        {{-- 替代 inline style，使用 CSS 控制無資料狀態 --}}
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
                            <a href="{{ $url }}" class="page-btn {{ $page == $newsList->currentPage() ? 'active' : '' }}">{{ $page }}</a>
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
