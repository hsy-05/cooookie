@extends('adminlte::page')

@section('title', '操作紀錄')

@section('content_header')
    <h1>操作紀錄</h1>
@stop

@section('content')
    {{-- 顯示訊息 --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h5><i class="icon fas fa-check"></i> 成功!</h5> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h5><i class="icon fas fa-ban"></i> 錯誤!</h5> {{ session('error') }}
        </div>
    @endif

    <div class="card">
        {{-- <div class="card-header">
            <h3 class="card-title">紀錄列表</h3>
        </div> --}}

        <div class="card-body">
            {{-- 1. 搜尋表單 --}}
            <form action="{{ route('admin.logs.index') }}" method="GET" class="mb-4 pb-3 border-bottom">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>關鍵字</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="搜尋內容/IP...">
                    </div>
                    <div class="col-md-4">
                        <label>日期區間</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                            <div class="input-group-append"><span class="input-group-text">~</span></div>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search"></i> 查詢</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-block">重置</a>
                    </div>
                </div>
            </form>

            {{-- 2. 批次刪除表單 --}}
            <form action="{{ route('admin.logs.batch_destroy') }}" method="POST" id="batchDeleteForm">
                @csrf
                @method('DELETE')

                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                    <div class="form-inline">
                        <label class="mr-2 text-danger"><i class="fas fa-trash-alt"></i> 批次刪除：</label>
                        <select name="delete_range" id="delete_range" class="form-control mr-2">
                            <option value="">-- 請選擇刪除範圍 (選此項則依勾選) --</option>
                            <option value="week">清除 一周前 的紀錄</option>
                            <option value="month">清除 一個月前 的紀錄</option>
                            <option value="half_year">清除 半年前 的紀錄</option>
                            <option value="year">清除 一年前 的紀錄</option>
                        </select>
                        <button type="button" class="btn btn-danger" id="batchDeleteBtn" disabled>
                            執行刪除
                        </button>
                    </div>
                    <div class="text-muted small">* 若選擇時間範圍，將忽略勾選框。</div>
                </div>

                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="table-check-col"><input type="checkbox" id="checkAll"></th>
                            <th class="text-center px-width-150">操作者</th>
                            <th class="text-center">操作紀錄 (內容)</th>
                            <th class="text-center px-width-150 hidden-md">IP 位置</th>
                            <th class="text-center px-width-150">操作時間</th>
                            <th class="text-center px-width-120">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="table-check-col">
                                    <input type="checkbox" name="ids[]" value="{{ $log->id }}" class="row-checkbox">
                                </td>
                                <td>{{ $log->admin->name }}</td>
                                <td>
                                    {{-- 使用 Model 定義的 action_color 存取器，取得對應的顏色 --}}
                                    <span class="badge badge-{{ $log->action_color }}">
                                        {{ $log->action }}
                                    </span>

                                    {{-- 使用 Model 定義的 action_log_info 存取器，確保文字不重複且不誤刪中間字眼 --}}
                                    {{ $log->action_log_info }}
                                </td>
                                <td class="text-center hidden-md">{{ $log->ip_address }}</td>
                                <td class="text-center">{{ $log->created_at }}</td>
                                <td class="text-left">
                                    <div class="table-actions-container">
                                        {{-- 刪除按鈕 --}}
                                        <form action="{{ route('admin.logs.destroy', $log->id) }}" method="POST"
                                            id="deleteForm{{ $log->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                data-id="{{ $log->id }}" title="刪除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">查無紀錄</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>

            <div class="mt-3">
                {{ $logs->appends(request()->all())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const deleteRange = document.getElementById('delete_range');
            const batchDeleteBtn = document.getElementById('batchDeleteBtn');
            const singleDeleteButtons = document.querySelectorAll('.js-delete-btn');

            // --- 1. 批次刪除按鈕狀態控制 ---
            function updateButtonState() {
                const isRangeSelected = deleteRange.value !== "";
                const isAnyChecked = Array.from(checkboxes).some(cb => cb.checked);
                batchDeleteBtn.disabled = !(isRangeSelected || isAnyChecked);
            }

            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateButtonState();
            });

            checkboxes.forEach(cb => cb.addEventListener('change', updateButtonState));
            deleteRange.addEventListener('change', updateButtonState);

            // --- 2. 批次刪除確認 ---
            batchDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (!batchDeleteBtn.disabled) {
                    const rangeText = deleteRange.options[deleteRange.selectedIndex]?.text;
                    let message = '';

                    if (deleteRange.value !== "") {
                        message = `警告：您即將【${rangeText}】。這將刪除大量資料，請再次確認。`;
                    } else {
                        const count = document.querySelectorAll('.row-checkbox:checked').length;
                        message = `您已勾選 <b>${count}</b> 筆資料，確定要刪除嗎？`;
                    }

                    showAlert('warning', '刪除確認', message, false, 'center', true, '確定刪除', 0, {
                        showCancelButton: true,
                        cancelButtonText: "取消",
                        preConfirm: () => {
                            document.getElementById('batchDeleteForm').submit();
                        }
                    });
                } else {
                    showAlert('error', '錯誤', '未選擇任何資料或刪除範圍。', false, 'center');
                }
            });

            // --- 3. 單筆刪除處理 ---
            singleDeleteButtons.forEach(button => {
                button.addEventListener('click', function() {

                    // 從 data-* 取得資料
                    const id = this.dataset.id;
                    const title = this.dataset.title || '確定刪除這筆資料嗎？';
                    const text = this.dataset.text || '刪除後無法恢復！';

                    // 呼叫共用刪除確認視窗
                    confirmDelete(id, title, text);
                });
            });
        });
    </script>
@stop
