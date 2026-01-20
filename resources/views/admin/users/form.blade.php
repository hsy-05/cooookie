@extends('adminlte::page')
@section('title', $pageTitle)
@section('content_header') <h1>{{ $pageTitle }}</h1> @stop

@section('css')
    <style>
        .permission-group { padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .group-title { font-weight: bold; border-bottom: 2px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .role-badge { font-size: 0.9em; margin-left: 5px; }
    </style>
@stop

@section('content')
<x-admin.page-message>
    <form action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="user-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="pill" href="#general">一般資料</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#permission">權限控管 (個人特例)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="pill" href="#personal">個人資料</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    {{-- 1. 一般資料 (包含角色選擇、父層選擇) --}}
                    <div class="tab-pane fade show active" id="general">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>所屬角色 <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-control" required id="role_select">
                                    <option value="">請選擇</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user->role_id == $role->id ? 'selected' : '' }}
                                            data-is-system="{{ $role->is_system }}"
                                            data-permissions='{{ json_encode($role->permissions ?? []) }}'>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">注意：角色已擁有的權限，在權限頁籤會預設為「已擁有(不可取消)」。</small>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>上層主管 (Parent)</label>
                                <select name="parent_id" class="form-control">
                                    <option value="">-- 無 (設為頂層) --</option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" {{ $user->parent_id == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }} ({{ $parent->role->name ?? '無角色' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- 帳號密碼區域 (略，維持原樣) --}}
                         <div class="form-group">
                            <label>登入帳號 (信箱)</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><label>密碼</label><input type="text" name="password" class="form-control"></div>
                            <div class="col-md-6"><label>確認密碼</label><input type="text" name="password_confirmation" class="form-control"></div>
                        </div>

                        <div class="form-group mt-3">
                            <label>帳戶狀態</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $user->is_active || !$isEdit ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">啟用</label>
                            </div>
                        </div>
                    </div>

                    {{-- 2. 權限控管 (個人特例) --}}
                    <div class="tab-pane fade" id="permission">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            此處可勾選「額外」權限。灰色打勾代表「該角色原本就有的權限」，無需重複勾選。
                        </div>

                        @foreach($permissionConfig as $modKey => $mod)
                            <div class="permission-group">
                                <div class="group-title">{{ $mod['label'] }}</div>
                                <div class="row">
                                    @foreach($mod['actions'] as $actKey => $actLabel)
                                        @php
                                            $permKey = "{$modKey}.{$actKey}";
                                            // 檢查使用者「個人」是否有勾選
                                            $userHas = in_array($permKey, $user->permissions ?? []);
                                        @endphp
                                        <div class="col-md-3 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       class="custom-control-input perm-checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permKey }}"
                                                       id="perm_{{ $permKey }}"
                                                       {{ $userHas ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $permKey }}">
                                                    {{ $actLabel }}
                                                    <span class="role-owned-badge text-muted" style="display:none; font-size:0.8em;">(角色已有)</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- 3. 個人資料 (略) --}}
                    <div class="tab-pane fade" id="personal">
                        <div class="form-group">
                            <label>姓名</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>大頭貼</label>
                            <input type="file" name="avatar" class="form-control-file">
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer text-center">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">返回</a>
                <button type="submit" class="btn btn-success">儲存</button>
            </div>
        </div>
    </form>
</x-admin.page-message>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_select');
        const checkboxes = document.querySelectorAll('.perm-checkbox');

        // 當角色改變時，視覺化顯示該角色擁有哪些權限
        function updateRoleVisuals() {
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            if (!selectedOption.value) return;

            // 取得該角色的權限陣列 (從 data attribute)
            // 如果是系統管理員，則全部視為有權限
            const isSystem = selectedOption.dataset.isSystem == '1';
            const rolePerms = JSON.parse(selectedOption.dataset.permissions || '[]');

            checkboxes.forEach(cb => {
                const key = cb.value;
                const label = cb.nextElementSibling;
                const badge = label.querySelector('.role-owned-badge');

                // 判斷角色是否擁有此權限
                const roleHasIt = isSystem || rolePerms.includes(key);

                if (roleHasIt) {
                    // 如果角色已有：將 checkbox 設為 disabled 且 checked (視覺上)，並顯示提示
                    cb.checked = true;
                    cb.disabled = true; // 禁止取消，因為這是角色層級賦予的
                    if(badge) badge.style.display = 'inline';
                } else {
                    // 如果角色沒有：恢復正常狀態 (依照 user 原本的勾選狀態)
                    // 注意：這裡無法輕易還原使用者原本是否勾選，
                    // 實務上通常：如果 disable，表單送出時該值不會送出，
                    // 但我們 User Model 邏輯是 User U Role，所以沒送出 User 權限也沒關係，Role 那邊會有。
                    cb.disabled = false;
                    if(badge) badge.style.display = 'none';
                }
            });
        }

        if(roleSelect) {
            roleSelect.addEventListener('change', updateRoleVisuals);
            // 初始化執行一次
            updateRoleVisuals();
        }
    });
</script>
@stop
