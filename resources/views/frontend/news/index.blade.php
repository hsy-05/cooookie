@extends('frontend.layouts.app')

@section('title', '消息列表')

@section('content')
  {{-- 橫幅區塊 --}}
  <div class="banner-section mb-4">
    <div class="banner-overlay">
      <div class="container text-center">
        <div class="banner-text py-5">
          <h2 class="display-4">最新消息</h2>
          <p>讓我們帶給你最新的消息與更新，敬請關注！</p>
        </div>
      </div>
    </div>
  </div>

  {{-- 設置消息列表容器 --}}
  <div class="news-list-container">
    <div class="row">
      <div class="col-md-8">
        <h2 class="mb-3">最新消息</h2>

        @foreach($newsList as $news)
          @php
            // 取當前語系的翻譯，否則用第一筆
            $desc = $news->descs->firstWhere('lang_id', $langId) ?: $news->descs->first();
          @endphp

          <article class="media mb-4 pb-3 border-bottom">
            @if($news->image)
              <img src="{{ asset('storage/' . $news->image) }}" class="mr-3" style="width:200px;height:130px;object-fit:cover;" alt="{{ $desc->title ?? '' }}">
            @endif
            <div class="media-body">
              <h5><a href="{{ route('news.show', $news->news_id) }}">{{ $desc->title ?? '無標題' }}</a></h5>
              <p class="text-muted small mb-1">{{ $news->created_at->format('Y-m-d') }}</p>
              <p class="mb-0 text-truncate" style="max-width:100%;">{!! strip_tags(Str::limit($desc->content ?? '', 180)) !!}</p>
            </div>
          </article>
        @endforeach

        {{-- 分頁 --}}
        <div class="mt-4">
          {{ $newsList->links() }}
        </div>
      </div>

      <aside class="col-md-4">
        {{-- 側欄：可放熱門消息或指定分類廣告 --}}
        <div class="card mb-3">
          <div class="card-body">
            <h6 class="card-title">最新公告</h6>
            {{-- 這裡示範取最新三則 --}}
            @foreach(\App\Models\News::where('is_visible',1)->latest()->limit(3)->get() as $side)
              @php $sd = $side->descs->firstWhere('lang_id',$langId) ?: $side->descs->first(); @endphp
              <div class="mb-2">
                <a href="{{ route('news.show', $side->news_id) }}">{{ $sd->title ?? '無標題' }}</a>
                <div class="text-muted small">{{ $side->created_at->format('Y-m-d') }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </aside>
    </div>
  </div>
@endsection
