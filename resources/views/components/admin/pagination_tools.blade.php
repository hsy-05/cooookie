<div class="d-flex justify-content-between align-items-center mt-3">
    {{-- 每頁筆數輸入框 --}}
    <form method="GET" class="form-inline">
        <label for="per_page" class="mr-2">每頁筆數：</label>
        <div class="input-group px-width-120">
            <input type="number"
                   name="per_page"
                   id="per_page"
                   class="form-control"
                   value="{{ session('admin_per_page', 8) }}"
                   min="1"
                   max="500">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit">套用</button>
            </div>
        </div>

        {{-- 自動保留其他參數 (搜尋、過濾等) --}}
        @foreach (request()->except('per_page', 'page') as $key => $value)
            @if (is_array($value))
                @foreach ($value as $subKey => $subValue)
                    <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
    </form>

    {{-- 資料統計 --}}
    <div>
        總計 <strong>{{ $items->total() }}</strong> 筆，
        第 <strong>{{ $items->currentPage() }}</strong> / {{ $items->lastPage() }} 頁
    </div>
</div>

{{-- 分頁按鈕 --}}
<div class="d-flex justify-content-center mt-3">
    {{ $items->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
</div>
