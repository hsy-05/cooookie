{{-- 麵包屑導航組件 --}}
<div class="breadcrumb-wrap js-fade-up">
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb__nav flex reset">
            {{-- 直接跑 Controller 傳過來的共享變數 --}}
            @isset($breadcrumbs)
                @foreach ($breadcrumbs as $crumb)
                    <li>
                        @if ($loop->last || empty($crumb['href']))
                            {{-- 最後一項不給連結，符合 SEO 與使用者體驗 --}}
                            <span class="current">{{ $crumb['text'] }}</span>
                        @else
                            <a href="{{ $crumb['href'] }}" title="{{ $crumb['text'] }}">
                                {{ $crumb['text'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            @endisset
        </ul>
    </nav>
</div>
