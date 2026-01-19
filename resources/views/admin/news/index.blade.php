@extends('adminlte::page')

@section('title', '消息管理')

@section('content_header')
    <h1>消息管理</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/backend.css') }}">
@stop

@section('content')
    <!-- 引入 x-admin.page-message 組件，用於顯示 session 訊息 -->
    <x-admin.page-message>
        <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
            <!-- 搜尋表單 -->
            <form action="{{ route('admin.news.index') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="搜尋標題..."
                        value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit">搜尋</button>
                        <!-- 如果有搜尋關鍵字，顯示清除按鈕 -->
                        @if ($search)
                            <a href="{{ route('admin.news.index', request()->except('search', 'page')) }}"
                                class="btn btn-light">清除</a>
                        @endif
                    </div>
                </div>
                <!-- 保留其他查詢參數，例如 per_page -->
                @foreach (request()->except('search', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            <!-- 新增按鈕 -->
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary mb-3 ml-auto">新增消息</a>
        </div>

        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center">標題</th>
                    {{-- <th class="text-center px-width-150 hidden-xs">圖片</th> --}}
                    <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                    <th class="text-center px-width-150 hidden-md">排序</th>
                    <th class="text-center px-width-150 hidden-sm">更新時間</th>
                    <th class="text-center px-width-120 table-actions">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($newsList as $item)
                    {{-- 使用 @forelse 處理無資料情況 --}}
                    <tr>
                        {{-- 確保顯示多語系標題，如果沒有則顯示 '--' --}}
                        <td>{{ $item->descs->first()->title ?? '--' }}</td>
                        {{--
                            <td>
                                @if ($item->image)
                                    <!-- 使用 asset() 輔助函數來生成正確的公共路徑 -->
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="" width="100">
                                @else
                                    無圖片
                                @endif
                            </td>
                        --}}
                        {{-- 是否顯示 --}}
                        <td class="text-center hidden-xs">
                            <!-- AdminLTE Custom Switch Element -->
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                    id="newsSwitch{{ $item->news_id }}" data-id="{{ $item->news_id }}" data-model="News"
                                    {{-- 指定模型名稱 --}} data-field="is_visible" {{-- 指定要更新的欄位 --}}
                                    {{ $item->is_visible ? 'checked' : '' }}>
                                <label class="custom-control-label" for="newsSwitch{{ $item->news_id }}"></label>
                            </div>
                        </td>
                        <td class="hidden-md text-center">{{ $item->display_order }}</td>
                        <td class="hidden-sm">{{ $item->updated_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn btn-sm btn-warning">編輯</a>
                            <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST"
                                style="display:inline-block;" id="deleteForm{{ $item->news_id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger js-delete-btn" data-id="{{ $item->news_id }}"
                                    data-title="確定刪除這筆資料嗎？" data-text="刪除後將無法恢復！">
                                    刪除
                                </button>

                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">目前沒有任何記錄。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- 分頁區域 -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <!-- 每頁筆數選擇 -->
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

                <!-- 保留其他查詢參數，包括搜尋關鍵字 -->
                @foreach (request()->except('per_page', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            <!-- 總頁數等資訊 -->
            <div>
                總計 {{ $newsList->total() }} 筆記錄，分 {{ $newsList->lastPage() }} 頁，目前第 {{ $newsList->currentPage() }} 頁
            </div>
        </div>

        <!-- 分頁按鈕獨立一行 -->
        <div class="d-flex justify-content-center mt-3">
            <!-- appends() 方法用於將當前請求的所有查詢參數（包括搜尋關鍵字和 per_page）添加到分頁連結中 -->
            {{ $newsList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </x-admin.page-message>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 取得所有刪除按鈕（可用在任何管理頁）
            const singleDeleteButtons = document.querySelectorAll('.js-delete-btn');

            singleDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {

                    // 從 data-* 取得資料
                    const id = this.dataset.id;
                    const title = this.dataset.title || '確定要刪除嗎？';
                    const text = this.dataset.text || '刪除後無法恢復！';

                    // 呼叫共用刪除確認視窗
                    confirmDelete(id, title, text);
                });
            });

        });
    </script>
@stop
