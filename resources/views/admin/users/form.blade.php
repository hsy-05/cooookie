@extends('adminlte::page')
@section('title', $pageTitle)
@section('content_header') <h1>{{ $pageTitle }}</h1> @stop

@section('content')
<x-admin.page-message>
    <form action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#general">一般資料</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#personal">個人資料</a></li>
            </ul>

            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="general">
                    <div class="form-group">
                        <label>圖片上傳</label>
                        <input type="file" name="avatar" class="form-control-file">
                        @if($user->avatar)
                            <img src="{{ asset('storage/'.$user->avatar) }}" width="100" class="mt-2 img-thumbnail">
                        @endif
                    </div>

                    <div class="form-group">
                        <label>角色選擇 <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-control" required>
                            <option value="">請選擇</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }} {{ $role->is_system ? '(系統最高)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>登入帳號 (信箱) <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>登入密碼 @if($isEdit) (不修改請留空) @else <span class="text-danger">*</span> @endif</label>
                            <input type="text" name="password" id="password" class="form-control"
                                   {{ !$isEdit ? 'required' : '' }}>
                        </div>
                        <div class="col-md-5 form-group">
                            <label>確認密碼</label>
                            <input type="text" name="password_confirmation" id="password_confirm" class="form-control"
                                   {{ !$isEdit ? 'required' : '' }}>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" onclick="generatePassword()">產生密碼</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>帳戶狀態</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                   value="1" {{ $user->is_active || !$isEdit ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">啟用</label>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="personal">
                    <div class="form-group">
                        <label>姓名 <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">返回</a>
            <button type="submit" class="btn btn-success">儲存</button>
        </div>
    </form>
</x-admin.page-message>

<script>
    function generatePassword() {
        // 產生 8 位數隨機密碼
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#";
        let password = "";
        for (let i = 0; i < 10; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('password').value = password;
        document.getElementById('password_confirm').value = password;
        alert('密碼已產生並填入：' + password);
    }
</script>
@stop
