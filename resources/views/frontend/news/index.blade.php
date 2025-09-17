@extends('frontend.layouts.app')

@section('title', '最新消息')

{{-- 定義 banner 區塊，讓 layout 可以取得 bannerSection 高度 --}}

<<<<<<< HEAD
    {{-- 頁面橫幅 --}}
    <div class="banner-section mb-4" style="background: url('{{ asset('images/banner-news.jpg') }}') center/cover no-repeat;">
        <div class="banner-overlay">
            <div class="container text-center">
                <div class="banner-text py-5">
                    <h2 class="display-4" data-aos="fade-up" data-aos-delay="100">最新消息</h2>
                    <p data-aos="fade-up" data-aos-delay="200">讓我們帶給你最新的消息與更新，敬請關注！</p>
=======
@section('content')
    <div class="banner-wrapper">
        {{-- 這邊用你的背景圖 + overlay --}}
        <div class="banner-bg" style="background: url('{{ asset('images/banner-news.jpg') }}') center/cover no-repeat;">
            <div class="banner-overlay">
                <div class="container text-center">
                    <div class="banner-text py-5">
                        <h2 class="display-4">最新消息</h2>
                        <p>讓我們帶給你最新的消息與更新，敬請關注！</p>
                    </div>
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
                </div>
            </div>
        </div>
    </div>

    {{-- 消息列表主區塊 --}}
    <div class="news-list-container">
        <div class="row">
            <div class="col-lg-8">
                {{-- 這裡的 h2 標題可以移除，因為橫幅已經有標題了，或者改為更小的副標題 --}}
                {{-- <h2 class="mb-3">最新消息</h2> --}}

                @foreach ($newsList as $news)
                    @php
                        $desc = $news->descs->firstWhere('lang_id', $langId) ?: $news->descs->first();
                    @endphp

<<<<<<< HEAD
                    <article class="news-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
=======
                    <article class="media mb-4 pb-3 border-bottom news-item" data-aos="fade-up" data-aos-duration="800">
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
                        @if ($news->image)
                            <img src="{{ asset('storage/' . $news->image) }}" class="news-thumb"
                                alt="{{ $desc->title ?? '' }}">
                        @endif
                        <div class="media-body">
                            <h5>
                                <a href="{{ route('news.show', $news->news_id) }}">{{ $desc->title ?? '無標題' }}</a>
                            </h5>
                            <p class="text-muted small mb-1">{{ $news->created_at->format('Y-m-d') }}</p>
                            <p class="mb-0">{!! strip_tags(Str::limit($desc->content ?? '', 180)) !!}</p>
                        </div>
                    </article>
                @endforeach

                <div class="mt-4">
                    {{ $newsList->links() }}
                </div>
            </div>

<<<<<<< HEAD
            {{-- 側邊欄 --}}
            <aside class="col-lg-4 news-sidebar mt-4 mt-lg-0">
=======
            <aside class="col-lg-4 mt-4 mt-lg-0">
>>>>>>> 436802640eb8903b21e45439ca1e4fc114810ff6
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">最新公告</h6>
                        @foreach (\App\Models\News::where('is_visible', 1)->latest()->limit(3)->get() as $side)
                            @php
                                $sd = $side->descs->firstWhere('lang_id', $langId) ?: $side->descs->first();
                            @endphp
                            <div class="mb-2" data-aos="fade-left" data-aos-delay="{{ $loop->index * 150 }}">
                                <a href="{{ route('news.show', $side->news_id) }}">{{ $sd->title ?? '無標題' }}</a>
                                <div class="text-muted small">{{ $sd->created_at->format('Y-m-d') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
