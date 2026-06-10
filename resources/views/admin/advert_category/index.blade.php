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
    {{-- 系統訊息顯示（成功 / 錯誤） --}}
    <x-admin.page-message>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    {{-- 提醒文字：因為這是系統架構層級，給工程師看的 --}}
                    <span class="text-danger small">
                        <i class="fas fa-tools"></i> 系統專用：此處僅限開發者調整廣告分類結構與參數。
                    </span>
                    <a href="{{ route('admin.advert_category.create') }}" class="btn btn-primary ml-auto">
                        <i class="fas fa-plus-square"></i> 新增廣告分類
                    </a>
                </div>

                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center px-width-100">ID</th>
                            <th class="text-center">分類代碼</th>
                            <th class="text-left">顯示名稱 (所有語系)</th>
                            <th class="text-center">啟用欄位</th>
                            <th class="text-center px-width-100">排序</th>
                            <th class="text-center px-width-150">是否顯示</th>
                            <th class="text-center px-width-200">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($catItems as $cat)
                            <tr>
                                <td class="text-center">{{ $cat->cat_id }}</td>
                                <td class="text-center"><code>{{ $cat->cat_code }}</code></td>
                                <td class="text-left">
                                    {{-- 比照 NewsCategory 的顯示邏輯：列出所有語系名稱以便快速核對 --}}
                                    @foreach ($cat->descs as $desc)
                                        <span class="badge badge-light border">{{ $desc->cat_name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    {{-- 顯示該分類啟用的廣告欄位 --}}
                                    @if(is_array($cat->cat_func_scope))
                                        @foreach($cat->cat_func_scope as $scope)
                                            <span class="badge badge-info">{{ $scope }}</span>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">{{ $cat->display_order }}</td>
                                <td class="text-center">
                                    {{-- 開關組件：與 News 完全統一，JS 自動綁定 toggle-boolean-switch --}}
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input toggle-boolean-switch"
                                               id="advSwitch{{ $cat->cat_id }}"
                                               data-id="{{ $cat->cat_id }}"
                                               data-model="{{ $modelName }}"
                                               data-field="is_visible"
                                               {{ $cat->is_visible ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="advSwitch{{ $cat->cat_id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions-container">
                                        {{-- 編輯 --}}
                                        <a href="{{ route('admin.advert_category.edit', $cat->cat_id) }}"
                                           class="btn btn-sm btn-warning" title="編輯">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- 刪除：使用 js-delete-btn 配合全站通用 SweetAlert 邏輯 --}}
                                        <form action="{{ route('admin.advert_category.destroy', $cat->cat_id) }}"
                                              method="POST" id="deleteForm{{ $cat->cat_id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                    data-id="{{ $cat->cat_id }}" title="刪除">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">目前沒有任何記錄。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-admin.page-message>
@stop
