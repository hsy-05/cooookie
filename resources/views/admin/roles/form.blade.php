@extends('adminlte::page')
@section('title', $pageTitle)
@section('content_header') <h1>{{ $pageTitle }}</h1> @stop

@section('css')
    <style>
        .permission-group { padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .group-title { font-weight: bold; border-bottom: 2px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; display: flex; justify-content: space-between; }
    </style>
@stop

@section('content')
<x-admin.page-message>
    <form action="{{ $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}" method="POST">
        @csrf @if($isEdit) @method('PUT') @endif

        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="role-tab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#general">一般資料</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#permission">權限控管</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    {{-- 一般資料 --}}
                    <div class="tab-pane fade show active" id="general">
                        <div class="form-group">
                            <label>角色名稱 <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" {{ $role->is_system ? 'readonly' : '' }}>
                        </div>
                        <div class="form-group">
                            <label>描述</label>
                            <textarea name="description" class="form-control" rows="3">{{ $role->description }}</textarea>
                        </div>
                    </div>

                    {{-- 權限控管 --}}
                    <div class="tab-pane fade" id="permission">
                        @if($role->is_system)
                            <div class="alert alert-info">此為系統最高權限角色，擁有所有權限。</div>
                        @else
                            <div class="mb-3">
                                <button type="button" class="btn btn-secondary btn-sm" id="btn-check-all">全選所有權限</button>
                                <button type="button" class="btn btn-light btn-sm" id="btn-uncheck-all">取消全選</button>
                            </div>

                            @foreach($permissionConfig as $modKey => $mod)
                                <div class="permission-group">
                                    <div class="group-title">
                                        <span>{{ $mod['label'] }}</span>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input group-select-all" id="group_all_{{ $modKey }}" data-target="group-{{ $modKey }}">
                                            <label class="custom-control-label font-weight-normal" for="group_all_{{ $modKey }}">本區全選</label>
                                        </div>
                                    </div>
                                    <div class="row group-{{ $modKey }}">
                                        @foreach($mod['actions'] as $actKey => $actLabel)
                                            @php
                                                $permKey = "{$modKey}.{$actKey}";
                                                // 取得該權限的依賴 (例如 delete 依賴 view)
                                                // 格式轉換為 json 字串供 JS 使用: ["news.view"]
                                                $depends = isset($mod['dependencies'][$actKey])
                                                    ? array_map(fn($d) => "{$modKey}.{$d}", $mod['dependencies'][$actKey])
                                                    : [];
                                            @endphp
                                            <div class="col-md-3 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                           class="custom-control-input perm-checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permKey }}"
                                                           id="perm_{{ $permKey }}"
                                                           data-depends='{{ json_encode($depends) }}'
                                                           {{ in_array($permKey, $role->permissions ?? []) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm_{{ $permKey }}">{{ $actLabel }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-default">取消</a>
                <button type="submit" class="btn btn-success">儲存設定</button>
            </div>
        </div>
    </form>
</x-admin.page-message>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAllBtn = document.getElementById('btn-check-all');
        const uncheckAllBtn = document.getElementById('btn-uncheck-all');
        const allCheckboxes = document.querySelectorAll('.perm-checkbox');
        const groupSelectAlls = document.querySelectorAll('.group-select-all');

        // 1. 全選所有
        if(checkAllBtn) {
            checkAllBtn.addEventListener('click', () => {
                allCheckboxes.forEach(cb => cb.checked = true);
                groupSelectAlls.forEach(cb => cb.checked = true);
            });
        }
        // 2. 取消全選
        if(uncheckAllBtn) {
            uncheckAllBtn.addEventListener('click', () => {
                allCheckboxes.forEach(cb => cb.checked = false);
                groupSelectAlls.forEach(cb => cb.checked = false);
            });
        }

        // 3. 單區全選
        groupSelectAlls.forEach(groupCb => {
            groupCb.addEventListener('change', function() {
                const targetClass = this.dataset.target;
                const targetCbs = document.querySelectorAll('.' + targetClass + ' .perm-checkbox');
                targetCbs.forEach(cb => cb.checked = this.checked);
            });
        });

        // 4. 依賴邏輯 (Dependency Logic)
        allCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // 如果勾選了某個權限
                if (this.checked) {
                    // 讀取 data-depends (例如 ["news.view"])
                    const depends = JSON.parse(this.dataset.depends || "[]");
                    depends.forEach(depKey => {
                        // 找到依賴的 checkbox 並勾選它
                        const depCb = document.getElementById('perm_' + depKey);
                        if (depCb) depCb.checked = true;
                    });
                } else {
                    // (選擇性) 如果取消了基礎權限(如 view)，是否要取消進階權限(如 delete)?
                    // 這邊邏輯比較複雜，通常建議單向連動(勾進階->自動勾基礎)即可，避免誤操作
                }
            });
        });
    });
</script>
@stop
