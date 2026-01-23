@extends('adminlte::page')

@section('title', '消息管理')

@section('content_header')
    <h1>消息管理</h1>
@stop

@section('content')
    {{-- 系統訊息顯示（成功 / 錯誤） --}}
    <x-admin.page-message>

        <div class="card">
            <div class="card-body">

                {{-- ===== 1. 搜尋 + 新增 ===== --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
                    {{-- 搜尋表單 --}}
                    <form action="{{ route('admin.news.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="搜尋標題..."
                                value="{{ $search ?? '' }}">

                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">搜尋</button>

                                {{-- 有搜尋條件才顯示清除 --}}
                                @if ($search)
                                    <a href="{{ route('admin.news.index', request()->except('search', 'page')) }}"
                                        class="btn btn-light">
                                        清除
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- 保留其他 GET 參數 --}}
                        @foreach (request()->except('search', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>

                    {{-- 新增按鈕 --}}
                    @can('news.create')
                        <a href="{{ route('admin.news.create') }}" class="btn btn-primary ml-auto">
                            新增消息
                        </a>
                    @endcan
                </div>

                {{-- ===== 2. 批次刪除表單 ===== --}}
                <form action="{{ route('admin.news.batch_destroy') }}" method="POST" id="batchDeleteForm">
                    @csrf
                    @method('DELETE')

                    {{-- 批次刪除工具列 --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                        <div class="form-inline">
                            <label class="mr-2 text-danger">
                                <i class="fas fa-trash-alt"></i> 批次刪除
                            </label>

                            <button type="button" class="btn btn-danger" id="batchDeleteBtn" disabled>
                                執行刪除
                            </button>
                        </div>

                        <div class="text-muted small">
                            * 請勾選要刪除的消息
                        </div>
                    </div>

                    {{-- ===== 3. 資料列表 ===== --}}
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                {{-- 全選 --}}
                                <th class="text-center px-width-50">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th class="text-center">標題</th>
                                <th class="text-center px-width-150 hidden-xs">是否顯示</th>
                                <th class="text-center px-width-150 hidden-md">排序</th>
                                <th class="text-center px-width-150 hidden-sm">更新時間</th>
                                <th class="text-center px-width-120">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($newsList as $item)
                                <tr>
                                    {{-- 勾選框 --}}
                                    <td class="text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $item->news_id }}"
                                            class="row-checkbox">
                                    </td>

                                    {{-- 標題（多語系取第一筆） --}}
                                    <td>{{ $item->descs->first()->title ?? '--' }}</td>

                                    {{-- 是否顯示 --}}
                                    <td class="text-center hidden-xs">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                                id="newsSwitch{{ $item->news_id }}" data-id="{{ $item->news_id }}"
                                                data-model="News" data-field="is_visible"
                                                {{ $item->is_visible ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="newsSwitch{{ $item->news_id }}"></label>
                                        </div>
                                    </td>

                                    {{-- 排序 --}}
                                    <td class="hidden-md text-center">
                                        {{ $item->display_order }}
                                    </td>

                                    {{-- 更新時間 --}}
                                    <td class="hidden-sm">
                                        {{ $item->updated_at->format('Y-m-d H:i') }}
                                    </td>

                                    {{-- 操作 --}}
                                    <td class="text-center">
                                        <div class="table-actions-container">

                                            {{-- 編輯 --}}
                                            @can('news.edit')
                                                <a href="{{ route('admin.news.edit', $item->news_id) }}"
                                                    class="btn btn-sm btn-warning" title="編輯">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- 單筆刪除 --}}
                                            @can('news.delete')
                                                <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST"
                                                    style="display:inline-block;" id="deleteForm{{ $item->news_id }}">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                        data-id="{{ $item->news_id }}" title="刪除">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        目前沒有任何記錄
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>

                {{-- ===== 4. 分頁設定 ===== --}}
                <div class="d-flex justify-content-between align-items-center mt-3">

                    {{-- 每頁筆數 --}}
                    <form id="perPageForm" method="GET" class="form-inline">
                        <label for="per_page" class="mr-2">每頁筆數：</label>
                        <select name="per_page" id="per_page" class="form-control"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach ([2, 5, 8, 15, 30] as $size)
                                <option value="{{ $size }}"
                                    {{ request('per_page', 8) == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>

                        @foreach (request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>

                    <div>
                        總計 {{ $newsList->total() }} 筆，
                        第 {{ $newsList->currentPage() }} / {{ $newsList->lastPage() }} 頁
                    </div>
                </div>

                {{-- 分頁按鈕 --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $newsList->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>

            </div>
        </div>
    </x-admin.page-message>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const batchDeleteBtn = document.getElementById('batchDeleteBtn');

            // 更新批次刪除按鈕狀態
            function updateButtonState() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                batchDeleteBtn.disabled = checkedCount === 0;
            }

            // 全選
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateButtonState();
            });

            // 單筆勾選
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateButtonState);
            });

            // 批次刪除確認
            batchDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const count = document.querySelectorAll('.row-checkbox:checked').length;

                showAlert(
                    'warning',
                    '刪除確認',
                    `您即將刪除 <b>${count}</b> 筆消息，刪除後無法恢復。`,
                    false,
                    'center',
                    true,
                    '確定刪除',
                    0, {
                        showCancelButton: true,
                        cancelButtonText: '取消',
                        preConfirm: () => {
                            document.getElementById('batchDeleteForm').submit();
                        }
                    }
                );
            });


            $(document).on('click', '.js-delete-btn', function() {
                // 從 data 屬性抓取參數，若無則給予預設值
                const id = $(this).data('id');
                const title = $(this).data('title') || '確定刪除這筆資料嗎？';
                const text = $(this).data('text') || '刪除後將無法恢復！';

                if (!id) {
                    console.warn('警告：刪除按鈕缺少 data-id 參數。');
                    return;
                }

                confirmDelete(id, title, text);
            });

        });
    </script>
@stop
