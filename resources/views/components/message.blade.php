@if (session('form_success'))
    @php
        $msg = session('form_success');
        $msg = is_array($msg) ? $msg : ['title' => $msg];
        $links = $msg['links'] ?? [];
    @endphp

    @if ($msg['msg_type'] == 0)
        <span class="blue bigger-125">
            <i class="ace-icon fa fa-info-circle"></i>
        </span>
    @elseif ($msg['msg_type'] == 1)
        <span class="blue bigger-125">
            <i class="ace-icon fa fa-warning"></i>
        </span>
    @else
        <span class="blue bigger-125">
            <i class="ace-icon fa fa-check"></i>
        </span>
    @endif

    <div id="form-success-box" class="alert alert-info text-center shadow-lg mx-auto mt-3"
        style="max-width: 600px; border-radius: 10px;">
        <h5 class="mb-2">ℹ️ {{ $msg['title'] ?? '操作完成' }}</h5>

        @if (!empty($links))
            <p>如果您不做出選擇，將在
                <span id="redirect-countdown">3</span> 秒後自動跳到下方的第一個選項。
            </p>
        @endif

        <div class="mt-3">
            @foreach ($links as $i => $link)
                <a href="{{ $link['href'] }}" class="btn {{ $i == 0 ? 'btn-primary' : 'btn-secondary' }} mr-2">
                    {{ $link['text'] }}
                </a>
            @endforeach
        </div>
    </div>

    @if (!empty($links) && ($msg['autoRedirect'] ?? false))
        <script>
            let countdown = 3;
            let countdownEl = document.getElementById('redirect-countdown');
            let targetUrl = "{{ $links[0]['href'] ?? '#' }}";

            let timer = setInterval(() => {
                countdown--;
                countdownEl.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = targetUrl;
                }
            }, 1000);
        </script>
    @endif
@else
    {{-- 平常顯示表單 --}}
    {{ $slot }}
@endif
