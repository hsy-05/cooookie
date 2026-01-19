@extends('adminlte::page')
@section('title', '網站管理員')
@section('content_header') <h1>網站管理員架構</h1> @stop

@section('content')
<x-admin.page-message>
    <div class="mb-3 text-right">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">新增管理員</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">管理員架構 / 姓名</th>
                        <th>帳號</th>
                        <th>角色</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 顯示目前使用者的頂層 (如果是系統管理員，則顯示所有頂層) --}}
                    {{-- 如果 Controller 回傳的是 Collection (一般管理員)，直接跑 --}}
                    @if($currentUser->role->is_system)
                        {{-- 系統管理員看全域 --}}
                        @foreach($users as $user)
                            @include('admin.users._user_row', ['user' => $user, 'level' => 0])
                        @endforeach
                    @else
                        {{-- 一般管理員只看得到自己(當作頂層)與下屬 --}}
                        {{-- 這裡為了呈現完整樹狀，我們手動先把自己印出來 --}}
                        @include('admin.users._user_row', ['user' => $currentUser, 'level' => 0])
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-admin.page-message>
@stop
