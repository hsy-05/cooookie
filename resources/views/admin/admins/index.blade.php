@extends('adminlte::page')

{{-- SEO 標題 --}}
@section('title', '後台管理 - 【COOOOKIE】')

{{-- 引用頁首組件 --}}
@component('components.admin.page_content_header', ['pageTitle' => '後台管理'])
    @slot('actions')
        @if(auth()->user()->canDo('create'))
            <a href="{{ route('admin.admins.create') }}"
               class="btn btn-primary shadow-sm px-4">
                <i class="fas fa-plus mr-1"></i>
                新增
            </a>
        @endif
    @endslot
@endcomponent

@section('content')
<x-admin.page-message>

<section class="table-section">
    <div class="card card-outline card-primary shadow-sm border-0">

        {{-- 工具列：包含搜尋框與提示區 --}}
        <div class="card-header tool-bar">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                {{-- 搜尋區 --}}
                <div class="search-wrapper mb-3 mb-md-0">
                    <div class="input-group search-input-group">
                        <input
                            type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="搜尋姓名、帳號或角色"
                            aria-label="搜尋"
                        >
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 提示資訊 --}}
                <div class="info-tag">
                    <span class="badge">
                        <i class="fas fa-info-circle mr-1"></i>
                        輸入關鍵字後，列表將即時過濾
                    </span>
                </div>

            </div>
        </div>

        {{-- 資料表格區域 --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th class="pl-4">姓名 / 組織架構</th>
                            <th class="px-width-250">電子郵件 (Email)</th>
                            <th class="px-width-200 hidden-md">賦予角色</th>
                            <th class="px-width-120 hidden-sm">帳號狀態</th>
                            <th class="text-center px-width-150">操作控制</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($admins as $admin)
                            @include('admin.admins.item_row', [
                                'admin' => $admin,
                                'level' => 0
                            ])
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">
                                        目前沒有符合條件的資料
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 分頁區塊 --}}
        @if(method_exists($admins, 'links'))
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-center">
                    {{ $admins->links() }}
                </div>
            </div>
        @endif
    </div>
</section>

{{-- 刪除表單（避免每列重複表單） --}}
<form id="global-delete-form" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

</x-admin.page-message>
@stop

@section('js')
<script>
/**
 * 即時搜尋與刪除防呆確認
 * - 搜尋會即時過濾表格資料
 * - 刪除時會先顯示確認對話框
 */
$(document).ready(function () {

    // 即時搜尋
    const $searchInput = $('#searchInput');
    const $rows = $('#dataTable tbody tr');

    $searchInput.on('keyup', function () {
        const keyword = $(this).val().toLowerCase();

        $rows.each(function () {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(keyword) !== -1);
        });
    });

});
</script>
@stop
