{{-- ▬ 模擬後台資料 ▬ --}}
@php
    $nav_breadcrumb = [
        ['href' => '/products', 'text' => '最新消息'],
        ['href' => '/products/123', 'text' => '消息標題1'],
    ];

@endphp
<div class="breadcrumb-wrap js-fade-up">
    <ul class="breadcrumb__nav flex reset">
        <li class="home"><a href="./" title="HOME">Home</a></li>
        @foreach ($nav_breadcrumb as $crumb)
            <li>
                <a href="{{ $crumb['href'] ?? '#' }}" title="{{ $crumb['text'] }}">{{ $crumb['text'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
