@extends('adminlte::page')
@section('title', '網站管理員')
@section('content_header') <h1>網站管理員</h1> @stop

@section('content')
<x-admin.page-message>
    <div class="mb-3 text-right">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">新增管理員</a>
    </div>

    {{-- 第一區塊：系統核心管理員 (只有系統管理員看得到這個區塊) --}}
    @if($systemRoots->isNotEmpty())
    <div class="card card-danger card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shield-alt"></i> 系統核心管理員 (System Admins)</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">架構 / 姓名</th>
                        <th>帳號</th>
                        <th>角色</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($systemRoots as $root)
                        @include('admin.users._user_row', ['user' => $root, 'level' => 0])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 第二區塊：一般網站管理員 --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> 網站管理團隊 (Site Managers)</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">架構 / 姓名</th>
                        <th>帳號</th>
                        <th>角色</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($normalRoots as $root)
                        @include('admin.users._user_row', ['user' => $root, 'level' => 0])
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">目前沒有一般管理員</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin.page-message>
@stop
