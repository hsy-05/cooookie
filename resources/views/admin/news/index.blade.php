@extends('adminlte::page')

@section('title', '消息管理')

@section('content_header')
    <h1>消息管理</h1>
@stop

@section('content')
    <div class="text-right">
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary mb-3">新增消息</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                {{-- <th>ID</th> --}}
                <th class="text-center">標題</th>
                <th class="text-center px-width-150 hidden-xs">圖片</th>
                <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                <th class="text-center px-width-150 hidden-xs">顯示排序</th>
                <th class="text-center px-width-150 hidden-xs">建立時間</th>
                <th class="text-center px-width-150 hidden-xs">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($newsList as $item)
                <tr>
                    {{-- <td>{{ $item->id }}</td> --}}
                    <td>{{ $item->title }}</td>
                    <td>
                        @if ($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="" width="100">
                        @endif
                    </td>
                    <td>
                        @if ($item->is_visible)
                            <span class="badge badge-success">顯示</span>
                        @else
                            <span class="badge badge-secondary">隱藏</span>
                        @endif
                    </td>
                    <td>{{ $item->display_order }}</td>
                    <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn btn-sm btn-warning">編輯</a>
                        <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST" style="display:inline-block;"
                            onsubmit="return confirm('確定要刪除嗎？')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">刪除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 分頁區域 --}}
    <div class="d-flex justify-content-between align-items-center">
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

            {{-- 保留其他查詢參數 --}}
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
    <div class="d-flex justify-content-center">
        {{ $newsList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
    </div>
@stop

@section('js')
    <script>
        {{-- Toast Success Message --}}
        @if (session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                $(document).Toasts('create', {
                    position: 'bottomRight',
                    class: 'bg-success',
                    title: '成功',
                    body: '{{ session('success') }}',
                    autohide: true,
                    delay: 3000
                });
            });
        @endif
    </script>
@endsection
