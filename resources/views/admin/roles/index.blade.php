@extends('adminlte::page')

@section('title', $pageTitle)

@section('content_header') <h1>{{ $pageTitle }}</h1> @stop

@section('content')
    <x-admin.page-message>
        <div class="card">
            <div class="card-body">
                {{-- 新增按鈕 --}}
                @can('roles.create')
                    <div class="mb-3 text-right">
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> 新增角色
                        </a>
                    </div>
                @endcan

                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">角色名稱</th>
                            <th class="text-center px-width-150">管理員數量</th>
                            <th class="text-center">描述</th>
                            <th class="text-center px-width-150">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>
                                    {{ $role->name }}
                                    @if ($role->isSuperRole())
                                        <span class="badge badge-danger">系統</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info px-2 py-1">{{ $role->users_count }}</span>
                                </td>
                                <td>{{ $role->description }}</td>

                                {{-- 操作欄位 --}}
                                <td class="text-center">
                                    <div class="table-actions-container">
                                        {{-- 編輯按鈕 --}}
                                        @can('roles.edit')
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning"
                                                title="編輯">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        {{-- 刪除按鈕 --}}
                                        @if (!$role->isSuperRole() && $role->users_count == 0)
                                            {{-- 只有非系統角色且沒有管理員的角色才顯示刪除按鈕 --}}
                                            @can('roles.delete')
                                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                                    style="display:inline-block;" id="deleteForm{{ $role->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger js-delete-btn"
                                                        data-id="{{ $role->id }}" title="刪除">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                    const title = this.dataset.title || '確定刪除這筆資料嗎？';
                    const text = this.dataset.text || '刪除後無法恢復！';

                    // 呼叫共用刪除確認視窗
                    confirmDelete(id, title, text);
                });
            });

        });
    </script>
@stop
