@extends('frontend.layouts.app')

@section('title', $desc->title ?? '消息內文')

@section('content')
  <div class="news-list-container"> {{-- 使用相同的容器樣式 --}}
    <div class="row">
      <div class="col-md-8">
        <h2 class="mb-3" data-aos="fade-right">{{ $desc->title ?? '' }}</h2>
        <p class="text-muted mb-4" data-aos="fade-right" data-aos-delay="100">{{ $news->created_at->format('Y-m-d') }}</p>

        @if($news->image)
          <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid mb-4 rounded" alt="{{ $desc->title }}" data-aos="zoom-in">
        @endif

        <article class="content" data-aos="fade-up">
          {{-- 假設 content 包含 HTML（從 DB 來），直接輸出 --}}
          {!! $desc->content ?? '' !!}
        </article>

        <div class="mt-5" data-aos="fade-up" data-aos-delay="200">
          <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">返回列表</a>
        </div>
      </div>

      <aside class="col-md-4 news-sidebar mt-4 mt-md-0"> {{-- 使用 news-sidebar 樣式 --}}
        @php
          $recent = \App\Models\News::where('is_visible',1)->latest()->limit(5)->get();
        @endphp
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">最新消息</h6>
            @foreach($recent as $r)
              @php $rd = $r->descs->firstWhere('lang_id',$desc->lang_id) ?: $r->descs->first(); @endphp
              <div class="mb-2" data-aos="fade-left" data-aos-delay="{{ $loop->index * 100 }}">
                <a href="{{ route('news.show',$r->news_id) }}">{{ $rd->title ?? '' }}</a>
                <div class="text-muted small">{{ $r->created_at->format('Y-m-d') }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </aside>
    </div>
  </div>
@endsection
