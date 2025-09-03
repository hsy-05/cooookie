@if (session('form_success'))
    @php
        $msg = session('form_success');
        $msg = is_array($msg) ? $msg : ['title' => $msg];
        $links = $msg['links'] ?? [];

        // 根據 msg_type 選擇 icon 與顏色
        switch ($msg['msg_type'] ?? 0) {
            case 1: // 警告
                $icon = 'fa-exclamation-triangle';
                $color = 'text-warning';
                $heading = '警告';
                break;
            case 2: // 成功
                $icon = 'fa-check-circle';
                $color = 'text-success';
                $heading = '成功';
                break;
            default: // 一般資訊
                $icon = 'fa-info-circle';
                $color = 'text-info';
                $heading = '資訊';
                break;
        }
    @endphp

    <div id="form-success-box" class="card shadow-sm mx-auto mt-4" style="max-width: 640px;">
        <div class="card-body text-center">
            {{-- 圖示 --}}
            <div class="mb-3">
                <i class="fa {{ $icon }} fa-3x {{ $color }}"></i>
            </div>

            {{-- 標題 --}}
            <h4 class="fw-bold mb-2">{{ $msg['title'] ?? $heading }}</h4>

            {{-- 倒數提示 --}}
            @if (!empty($links))
                <p class="text-muted">
                    如果您不做出選擇，將在
                    <span id="redirect-countdown">3</span> 秒後自動跳到下方的第一個選項。
                </p>
            @endif

            {{-- 連結按鈕 --}}
            <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                @foreach ($links as $i => $link)
                    <a href="{{ $link['href'] }}"
                       class="btn {{ $i === 0 ? 'btn-primary' : 'btn-secondary' }} mr-2">
                        {{ $link['text'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 倒數計時 --}}
    @if (!empty($links) && ($msg['autoRedirect'] ?? false))
        <script>
            (function(){
                let countdown = 3;
                const el = document.getElementById('redirect-countdown');
                const target = "{{ $links[0]['href'] ?? '#' }}";
                const timer = setInterval(() => {
                    countdown--;
                    if (el) el.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        window.location.href = target;
                    }
                }, 1000);
            })();
        </script>
    @endif
@else
    {{-- 沒有成功訊息 → 顯示 slot (通常是表單) --}}
    {{ $slot }}
@endif
