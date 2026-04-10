@extends('adminlte::page')

{{-- 瀏覽器分頁標題：依然使用完整標題，方便搜尋與辨識 --}}
@section('title', $pageTitle)

{{-- 頁面內容區標題：使用組件並傳入預先處理好的標題物件 --}}
@component('components.admin.page_content_header', [
    'pageTitle' => $pageTitle, // 傳入字串（供組件內的備援邏輯使用）
    'titleConfig' => $titleConfig, // [關鍵] 傳入物件，讓組件直接抓 $titleConfig['main']
])
    @slot('actions')
        {{-- 權限判斷：確保有權限的人才看得到按鈕 --}}
        @if (auth()->user()->canDo($permissionName . '.create'))
            <a href="{{ route('admin.' . $permissionName . '.create') }}" class="btn btn-primary shadow-sm px-4">
                <i class="fas fa-plus mr-1"></i>
                新增
            </a>
        @endif
    @endslot
@endcomponent

@section('content')
    <x-admin.page-message>

        <div class="card">
            <div class="card-body">

                {{-- ===== 搜尋 + 新增 ===== --}}
                <div class="d-flex justify-content-between align-items-center mb-3 px-width-600">
                    {{-- 搜尋表單 --}}
                    <form action="{{ route('admin.contact.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="搜尋標題..."
                                value="{{ $search ?? '' }}">

                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">搜尋</button>

                                {{-- 有搜尋條件才顯示清除 --}}
                                @if ($search)
                                    <a href="{{ route('admin.contact.index', request()->except('search', 'page')) }}"
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

                </div>

                <form action="{{ route('admin.contact.batch_destroy') }}" method="POST" id="batchDeleteForm">
                    @csrf
                    @method('DELETE')

                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th class="text-center">諮詢編號</th>
                                    <th class="text-center">聯絡人</th>
                                    <th class="text-center">諮詢主旨</th>
                                    <th class="text-center px-width-100 hidden-xs">狀態</th>
                                    <th class="text-center px-width-150 hidden-sm">回覆時間</th>
                                    <th class="text-center px-width-150 hidden-sm">諮詢時間</th>
                                    <th class="text-center px-width-120">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contactList as $item)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="ids[]" value="{{ $item->contact_id }}" class="row-checkbox">
                                        </td>
                                        <td class="text-center">{{ $item->contact_sn }}</td>
                                        <td>
                                            <strong>{{ $item->fullname }}</strong><br>
                                            <small class="text-muted">{{ $item->email }}</small>
                                        </td>
                                        <td>{{ $item->subject }}</td>
                                        <td class="text-center">
                                            @if($item->status == 'replied')
                                                <span class="badge badge-success px-2 py-1">已回覆</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1">待處理</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->updated_at->format('Y-m-d H:i') }}</td>
                                        <td class="text-center">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                        {{-- 操作 --}}
                                        <td>
                                            <div class="table-actions-container">
                                                {{-- 編輯 --}}
                                                @can('contact.edit')
                                                    <a href="{{ route('admin.contact.edit', $item->contact_id) }}"
                                                        class="btn btn-sm btn-warning" title="編輯">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                {{-- 單筆刪除 --}}
                                                @can('contact.delete')
                                                    <form action="{{ route('admin.contact.destroy', $item->contact_id) }}" method="POST"
                                                        id="deleteForm{{ $item->contact_id }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                            data-id="{{ $item->contact_id }}" title="刪除">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">暫無諮詢訊息</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded d-flex align-items-center">
                        <button type="button" class="btn btn-danger mr-3" id="batchDeleteBtn" disabled>執行批次刪除</button>
                        <small class="text-secondary">* 刪除後資料無法復原，請謹慎操作</small>
                    </div>
                </form>

                {{-- ===== 分頁與工具區塊 ===== --}}
                @include('components.admin.pagination_tools', ['items' => $contactList])
            </div>
        </div>
    </x-admin.page-message>
@stop
