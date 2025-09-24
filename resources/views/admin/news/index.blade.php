@extends('adminlte::page')

@section('title', '消息管理')

@section('content_header')
    <h1>消息管理</h1>
@stop

@section('content')
    {{-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 --}}
    <x-admin.page-message>
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- 搜尋表單 --}}
            <form action="{{ route('admin.news.index') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜尋標題..." value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit">搜尋</button>
                        {{-- 如果有搜尋關鍵字，顯示清除按鈕 --}}
                        @if ($search)
                            <a href="{{ route('admin.news.index', request()->except('search', 'page')) }}"
                                class="btn btn-light">清除</a>
                        @endif
                    </div>
                </div>
                {{-- 保留其他查詢參數，例如 per_page --}}
                @foreach (request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            {{-- 新增消息按鈕 --}}
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">新增消息</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">標題</th>
                    <th class="text-center px-width-150 hidden-xs">圖片</th>
                    <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                    <th class="text-center px-width-150 hidden-xs">顯示排序</th>
                    <th class="text-center px-width-150 hidden-xs">建立時間</th>
                    <th class="text-center px-width-150 hidden-xs">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($newsList as $item)
                    {{-- 使用 @forelse 處理無資料情況 --}}
                    <tr>
                        {{-- 確保顯示多語系標題，如果沒有則顯示 '--' --}}
                        <td>{{ $item->descs->first()->title ?? '--' }}</td>
                        <td>
                            @if ($item->image)
                                {{-- 使用 asset() 輔助函數來生成正確的公共路徑 --}}
                                <img src="{{ asset('storage/' . $item->image) }}" alt="" width="100">
                            @else
                                無圖片
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- AdminLTE Custom Switch Element --}}
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                    id="newsSwitch{{ $item->news_id }}" data-id="{{ $item->news_id }}" data-model="News"
                                    {{-- 指定模型名稱 --}} data-field="is_visible" {{-- 指定要更新的欄位 --}}
                                    {{ $item->is_visible ? 'checked' : '' }}>
                                <label class="custom-control-label" for="newsSwitch{{ $item->news_id }}"></label>
                            </div>
                        </td>
                        <td>{{ $item->display_order }}</td>
                        <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn btn-sm btn-warning">編輯</a>
                            <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('確定要刪除嗎？')"> {{-- 傳統的 JS confirm 提示 --}}
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">沒有找到相關消息。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- 分頁區域 --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            {{-- 每頁筆數選擇 --}}
            <form id="perPageForm" method="GET" class="form-inline">
                <label for="per_page" class="mr-2">每頁筆數：</label>
                <select name="per_page" id="per_page" class="form-control"
                    onchange="document.getElementById('perPageForm').submit()">
                    @foreach ([2, 5, 8, 15, 30] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 8) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>

                {{-- 保留其他查詢參數，包括搜尋關鍵字 --}}
                @foreach (request()->except('per_page', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            {{-- 總頁數等資訊 --}}
            <div>
                總計 {{ $newsList->total() }} 筆記錄，分 {{ $newsList->lastPage() }} 頁，目前第 {{ $newsList->currentPage() }} 頁
            </div>
        </div>

        {{-- 分頁按鈕獨立一行 --}}
        <div class="d-flex justify-content-center mt-3">
            {{-- appends() 方法用於將當前請求的所有查詢參數（包括搜尋關鍵字和 per_page）添加到分頁連結中 --}}
            {{ $newsList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </x-admin.page-message>
@stop

@section('js')
    {{-- 引入 SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            // 監聽所有帶有 'toggle-boolean-switch' 類別的 checkbox 的 change 事件
            $('.toggle-boolean-switch').on('change', function() {
                const switchElement = $(this);
                const id = switchElement.data('id'); // 從 data-id 屬性獲取記錄 ID
                const model = switchElement.data('model'); // 從 data-model 屬性獲取模型名稱
                const field = switchElement.data('field'); // 從 data-field 屬性獲取要更新的欄位名稱
                // 獲取 checkbox 的新狀態 (true/false)，並轉換為 1/0，以便 Laravel 的 boolean 驗證器正確處理
                const value = switchElement.is(':checked') ? 1 : 0;

                // 發送 AJAX 請求
                $.ajax({
                    url: "{{ route('admin.toggle.boolean') }}", // 通用 AJAX 路由
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // 包含 CSRF token 以通過 Laravel 的 CSRF 保護
                        model: model,
                        id: id,
                        field: field,
                        value: value // 使用轉換後的 1 或 0
                    },
                    success: function(response) {
                        // 顯示成功訊息 (使用 SweetAlert2)
                        Swal.fire({
                            icon: 'success',
                            title: '成功',
                            text: response.message,
                            toast: true, // 啟用 Toast 模式 (右上角彈出)
                            position: 'top-end', // 彈出位置
                            showConfirmButton: false, // 不顯示確認按鈕
                            timer: 3000 // 3 秒後自動關閉
                        });
                    },
                    error: function(xhr) {
                        // 顯示錯誤訊息 (使用 SweetAlert2)
                        let errorMessage = '狀態更新失敗。';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                            // 如果後端返回了驗證錯誤，可以顯示更詳細的訊息
                            if (xhr.responseJSON.errors) {
                                for (const key in xhr.responseJSON.errors) {
                                    errorMessage += '\n' + xhr.responseJSON.errors[key].join(
                                        ', ');
                                }
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: '錯誤',
                            text: errorMessage,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // 如果更新失敗，將 switch 恢復到之前的狀態，確保 UI 與實際資料庫狀態一致
                        switchElement.prop('checked', !value);
                    }
                });
            });

            // 檢查是否有 form_success session 訊息，並顯示 SweetAlert2 提示
            @if (session('form_success'))
                Swal.fire({
                    icon: 'success',
                    title: '成功',
                    text: '{{ session('form_success') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif

            // 檢查是否有 form_error session 訊息，並顯示 SweetAlert2 提示
            @if (session('form_error'))
                Swal.fire({
                    icon: 'error',
                    title: '錯誤',
                    text: '{{ session('form_error') }}',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            @endif
        });
    </script>
@stop
