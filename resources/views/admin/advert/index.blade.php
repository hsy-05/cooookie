@extends('adminlte::page')

@section('title', '廣告管理')

@section('content_header')
    <h1>廣告管理</h1>
@stop

@section('content')
    {{-- 系統訊息顯示（成功 / 錯誤） --}}
    <x-admin.page-message>

        <div class="card">
            <div class="card-body">

                {{-- ===== 1. 搜尋 + 新增 ===== --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
                    {{-- 搜尋表單 --}}
                    <form action="{{ route('admin.advert.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="搜尋廣告名稱..."
                                value="{{ $search ?? '' }}">

                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">搜尋</button>

                                {{-- 有搜尋條件才顯示清除 --}}
                                @if ($search)
                                    <a href="{{ route('admin.advert.index', request()->except('search', 'page')) }}"
                                        class="btn btn-light">
                                        清除
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- 保留其他 GET 參數 (如分頁、分類等) --}}
                        @foreach (request()->except('search', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>

                    {{-- 新增按鈕 --}}
                    @can('advert.create')
                        <a href="{{ route('admin.advert.create') }}" class="btn btn-primary ml-auto">
                            新增廣告
                        </a>
                    @endcan
                </div>

                {{-- ===== 2. 批次刪除表單 (如廣告管理有支援批次刪除可保留，若無可註解) ===== --}}
                <form action="#" method="POST" id="batchDeleteForm">
                    @csrf
                    @method('DELETE')

                    {{-- 批次操作工具列 --}}
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
                            * 請勾選要刪除的廣告項目
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
                                <th class="text-center">廣告名稱</th>
                                <th class="text-center px-width-150 hidden-xs">分類</th>
                                <th class="text-center px-width-150 hidden-md">廣告圖</th>
                                <th class="text-center px-width-100 hidden-md">排序</th>
                                <th class="text-center px-width-120 hidden-xs">是否顯示</th>
                                <th class="text-center px-width-120">操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($adverts as $item)
                                <tr>
                                    {{-- 勾選框 --}}
                                    <td class="text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $item->adv_id }}"
                                            class="row-checkbox">
                                    </td>

                                    {{-- 廣告名稱 (多語系取第一筆或預設) --}}
                                    <td>{{ $item->descs->first()->adv_name ?? '--' }}</td>

                                    {{-- 分類代碼 --}}
                                    <td class="text-center hidden-xs">
                                        <span class="badge badge-info">{{ $item->category->cat_code ?? '未分類' }}</span>
                                    </td>

                                    {{-- 廣告預覽圖 --}}
                                    <td class="text-center hidden-md">
                                        @if ($item->adv_img_url)
                                            <img src="{{ asset('storage/' . $item->adv_img_url) }}"
                                                    class="img-thumbnail" style="max-height: 50px;" alt="廣告預覽">
                                        @else
                                            <span class="text-muted small">無圖片</span>
                                        @endif
                                    </td>

                                    {{-- 排序 --}}
                                    <td class="text-center hidden-md">
                                        {{ $item->display_order }}
                                    </td>

                                    {{-- 是否顯示 (開關切換) --}}
                                    <td class="text-center hidden-xs">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                                id="advSwitch{{ $item->adv_id }}" data-id="{{ $item->adv_id }}"
                                                data-model="Advert" data-field="is_visible"
                                                {{ $item->is_visible ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="advSwitch{{ $item->adv_id }}"></label>
                                        </div>
                                    </td>

                                    {{-- 操作按鈕 --}}
                                    <td class="text-center">
                                        <div class="table-actions-container">
                                            {{-- 編輯 --}}
                                            @can('advert.edit')
                                                <a href="{{ route('admin.advert.edit', $item->adv_id) }}"
                                                    class="btn btn-sm btn-warning" title="編輯">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- 單筆刪除 --}}
                                            @can('advert.delete')
                                                <form action="{{ route('admin.advert.destroy', $item->adv_id) }}" method="POST"
                                                     id="deleteForm{{ $item->adv_id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                        data-id="{{ $item->adv_id }}"
                                                        data-title="確定刪除此廣告？"
                                                        data-text="刪除後圖片與資料將無法恢復。"
                                                        title="刪除">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        目前沒有任何廣告記錄
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>

                {{-- ===== 4. 分頁設定 ===== --}}
                <div class="d-flex justify-content-between align-items-center mt-3">

                    {{-- 每頁筆數切換 --}}
                    <form id="perPageForm" method="GET" class="form-inline">
                        <label for="per_page" class="mr-2">每頁筆數：</label>
                        <select name="per_page" id="per_page" class="form-control"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach ([2, 8, 15, 30, 50] as $size)
                                <option value="{{ $size }}"
                                    {{ request('per_page', 8) == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>

                        {{-- 切換筆數時保留搜尋關鍵字 --}}
                        @foreach (request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>

                    <div>
                        總計 {{ $adverts->total() }} 筆，
                        第 {{ $adverts->currentPage() }} / {{ $adverts->lastPage() }} 頁
                    </div>
                </div>

                {{-- 分頁按鈕 --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $adverts->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                </div>

            </div>
        </div>
    </x-admin.page-message>
@stop

@section('js')
    <script>
    </script>
@stop
