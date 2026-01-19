@extends('adminlte::page')
@section('title', '角色管理')
@section('content_header') <h1>角色管理</h1> @stop

@section('content')
<x-admin.page-message>
    <div class="mb-3 text-right">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">新增角色</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>角色名稱</th>
                <th width="120" class="text-center">管理員數量</th>
                <th>描述</th>
                <th width="150" class="text-center">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr class="{{ $role->is_system ? 'table-warning' : '' }}">
                <td>
                    {{ $role->name }}
                    @if($role->is_system) <span class="badge badge-danger">系統</span> @endif
                </td>
                <td class="text-center">
                    {{-- 顯示關聯數量 --}}
                    <span class="badge badge-info" style="font-size: 1rem;">{{ $role->users_count }}</span>
                </td>
                <td>{{ $role->description }}</td>
                <td class="text-center">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning">編輯</a>
                    @if(!$role->is_system && $role->users_count == 0)
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('確定刪除?')">刪除</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-admin.page-message>
@stop
