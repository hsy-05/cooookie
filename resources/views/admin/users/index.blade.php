@extends('adminlte::page')

@section('title', '網站管理員')

{{--
    專業優化：
    如果 User Model 中有 preferences 設定，我們可以用 JS 將其應用到 Body 上。
    這通常在 Layout 處理，但為了符合你的要求 (只改這裡)，我們寫在 JS stack。
--}}

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">網站管理員</h1>
        @if(auth()->user()->canDo('users.create'))
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary elevation-2">
                <i class="fas fa-user-plus mr-1"></i> 新增成員
            </a>
        @endif
    </div>
@stop

@section('content')
<x-admin.page-message>
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title">
                <i class="fas fa-sitemap mr-1"></i> 組織架構列表
            </h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="搜尋姓名/帳號...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="bg-light">
                        <th style="width: 35%">姓名 / 架構</th>
                        <th style="width: 25%">帳號 (Email)</th>
                        <th style="width: 15%" class="hidden-md">角色身分</th>
                        <th style="width: 10%" class="hidden-sm">狀態</th>
                        <th style="width: 15%" class="text-center">功能</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        {{-- 呼叫遞迴 Blade --}}
                        @include('admin.users._user_row', ['user' => $user, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                <p>目前尚無資料</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- 如果有分頁，建議加上 $users->links() --}}
    </div>

    {{-- 隱藏的刪除表單 --}}
    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>
</x-admin.page-message>
@stop

@section('js')
<script>
$(document).ready(function() {
    // 刪除確認
    $('.js-delete-btn').on('click', function() {
        let url = $(this).data('url'); // 注意：_user_row 必須把 route 放進 data-url
        // 或者保留原本 form submit 方式亦可，這裡示範更簡潔的寫法
        let formId = $(this).closest('form').attr('id');

        if(confirm('警告：確定要刪除此帳號嗎？\n刪除後無法復原，且可能影響其下屬結構。')) {
            $('#' + formId).submit();
        }
    });

    // 搜尋功能 (前端簡易過濾)
    $('input[name="table_search"]').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
@stop
