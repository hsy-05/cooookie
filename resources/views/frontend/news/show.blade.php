@extends('frontend.layouts.app')

@section('title', $desc->title ?? '消息內文')

@section('content')
  <div class="row">
    <div class="col-md-8">
      <h2>{{ $desc->title ?? '' }}</h2>
      <p class="text-muted">{{ $news->created_at->format('Y-m-d') }}</p>

      @if($news->image)
        <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid mb-3" alt="{{ $desc->title }}">
      @endif

      <article class="content">
        {{-- 假設 content 包含 HTML（從 DB 來），直接輸出 --}}
        {!! $desc->content ?? '' !!}
      </article>

      <div class="mt-4">
        <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">返回列表</a>
      </div>
    </div>

    <aside class="col-md-4">
      {{-- 可重用側欄 --}}
      @php
        $recent = \App\Models\News::where('is_visible',1)->latest()->limit(5)->get();
      @endphp
      <div class="card">
        <div class="card-body">
          <h6>最新消息</h6>
          @foreach($recent as $r)
            @php $rd = $r->descs->firstWhere('lang_id',$desc->lang_id) ?: $r->descs->first(); @endphp
            <div class="mb-2"><a href="{{ route('news.show',$r->news_id) }}">{{ $rd->title ?? '' }}</a></div>
          @endforeach
        </div>
      </div>
    </aside>
  </div>
@endsection
