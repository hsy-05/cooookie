@extends('frontend.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}">
@endpush

@section('content')

    {{-- 頁面橫幅標題區 --}}
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
                {{-- 使用語意化 <nav> 標籤，並透過 data-attributes 處理動畫特效 --}}
                <nav class="aside-nav slide js-fade-up" data-delay="0.2">

                    {{-- 行動裝置（平板與手機）專用的下拉選單觸發按鈕，預設在電腦版會被 CSS 隱藏 --}}
                    <div class="aside-nav__btn" id="js-aside-nav-btn">
                        {{-- 這裡會透過 jQuery 自動檢查哪一個分類有 .active，並將其名稱動態塞入 --}}
                        <span class="word" id="js-aside-nav-current-text">選擇分類</span>
                        {{-- 右側的加減號或箭頭圖示 --}}
                        <span class="icon"></span>
                    </div>

                    {{-- 分類連結清單，電腦版與手機版共用此單一結構，不重複執行 foreach --}}
                    <ul class="aside-nav__tab flex reset" id="js-aside-nav-list">
                        {{-- 「全部消息」按鈕 --}}
                        <li class="{{ !$categoryId ? 'current' : '' }}">
                            <a href="{{ route('news.index') }}" title="全部消息">
                                <span>全部消息</span>
                            </a>
                        </li>

                        {{-- 迴圈跑出其餘資料庫分類 --}}
                        @foreach ($catList as $cat)
                            @php
                                // 預先取得語系名稱，若沒有則給予預設值，確保 Blade 乾淨
                                $catName = $cat->descs->first()->name ?? '未命名';
                                // 檢查當前頁面是否為該分類
                                $isCurrent = $categoryId == $cat->cat_id ? 'current' : '';
                            @endphp
                            <li class="{{ $isCurrent }}">
                                <a href="{{ route('news.category', $cat->cat_id) }}" title="{{ $catName }}">
                                    <span>{{ $catName }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                {{-- 消息網格 --}}
                <div class="news-grid">
                    @forelse ($items as $item)
                        <article class="news-card js-fade-up" data-delay="0.2">
                            <a href="{{ route('news.show', ['news' => $item->news_id]) }}"
                                title="{{ $item->currentDesc->title ?? '' }}">
                                {{-- 圖片區 500*360 --}}
                                <div class="n-img-box">
                                    <img src="{{ $item->image_url ? asset('storage/' . $item->image_url) : asset('images/default-news.jpg') }}"
                                        alt="{{ $item->currentDesc->title ?? '' }}" class="n-img" loading="lazy">
                                </div>

                                {{-- 資訊區 --}}
                                <div class="n-info">
                                    <div class="n-meta">
                                        @if ($item->category)
                                            <span class="n-cat-label">{{ $item->category->currentDesc->name }}</span>
                                        @endif
                                        <time class="n-date" datetime="{{ $item->created_at->format('Y-m-d') }}">
                                            {{ $item->created_at->format('Y.m.d') }}
                                        </time>
                                    </div>
                                    <h2 class="n-title">{{ $item->currentDesc->title ?? 'Untitled' }}</h2>
                                    <p class="n-desc">{{ Str::limit(strip_tags($item->currentDesc->content ?? ''), 80) }}
                                    </p>
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
                @if ($items->hasPages())
                    <nav class="pagination-wrap js-fade-up">
                        @if (!$items->onFirstPage())
                            <a href="{{ $items->previousPageUrl() }}" class="page-btn" rel="prev">&larr;</a>
                        @endif

                        @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                                class="page-btn {{ $page == $items->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach

                        @if ($items->hasMorePages())
                            <a href="{{ $items->nextPageUrl() }}" class="page-btn" rel="next">&rarr;</a>
                        @endif
                    </nav>
                @endif

            </div>
        </section>
    </div>
@endsection
