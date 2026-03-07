@extends('adminlte::page')

@section('title', $pageTitle)

@section('content_header')
    <h1>{{ $pageTitle }}</h1>
@stop

@section('content')
<x-admin.page-message>
    <form action="{{ $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card card-primary card-outline card-outline-tabs">
            {{-- 頁籤標題區 --}}
            <x-slot:tabs>
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab">一般資料</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="pill" href="#tab-permission" role="tab">權限控管</a>
                </li>
            </x-slot:tabs>


            <div class="card-body">
                <div class="tab-content">
                    {{-- 分頁1：一般資料 --}}
                    <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                        <div class="form-group">
                            <label>角色名稱 <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" {{ $role->is_system ? 'readonly' : '' }} required>
                        </div>
                        <div class="form-group">
                            <label>描述</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                        </div>
                    </div>

                    {{-- 分頁2：權限控管 --}}
                    <div class="tab-pane fade" id="tab-permission" role="tabpanel">
                        @if($role->is_system)
                            <div class="alert alert-info">此為系統最高權限角色，擁有所有權限。</div>
                        @else
                            <div class="mb-3">
                                <button type="button" class="btn btn-secondary btn-sm js-bulk-check" data-mode="all">全選所有</button>
                                <button type="button" class="btn btn-light btn-sm js-bulk-check" data-mode="none">取消全選</button>
                            </div>

                            <div class="permission-wrapper">
                                @foreach($permissions as $modKey => $mod)
                                {{-- 外層卡片：代表一個大選單分類（例如：消息管理） --}}
                                <div class="card card-outline card-secondary mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title font-weight-bold">{{ $mod['label'] }}</h3>
                                        <div class="card-tools">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input js-group-select" id="group_{{ $modKey }}" data-target="group-{{ $modKey }}">
                                                <label class="custom-control-label font-weight-normal" for="group_{{ $modKey }}">全選本區</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0 group-{{ $modKey }}">
                                        <table class="table table-hover mb-0">
                                            <tbody>
                                                @foreach($mod['subs'] as $subKey => $sub)
                                                {{-- 內層：代表子選單（例如：最新消息） --}}
                                                <tr>
                                                    <td width="200" class="bg-light font-weight-bold border-right">
                                                        {{ $sub['label'] }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap">
                                                            @foreach($sub['actions'] as $action)
                                                            <div class="custom-control custom-checkbox mr-4 mb-2">
                                                                <input type="checkbox"
                                                                    name="permissions[]"
                                                                    value="{{ $action['key'] }}"
                                                                    id="perm_{{ str_replace('.', '_', $action['key']) }}"
                                                                    class="custom-control-input js-perm-cb"
                                                                    data-depends='{{ $action['depends'] }}'
                                                                    {{ in_array($action['key'], $role->permissions ?? []) ? 'checked' : '' }}>
                                                                <label class="custom-control-label font-weight-normal" for="perm_{{ str_replace('.', '_', $action['key']) }}">
                                                                    {{ $action['label'] }}
                                                                </label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-default mr-2">取消返回</a>
                <button type="submit" class="btn btn-success px-4 js-submit-btn">儲存設定</button>
            </div>
        </div>
    </form>
</x-admin.page-message>
@stop

@section('js')
<script>
$(function() {
    /**
     * 初始化：建立反向依賴地圖 (讓基礎權限知道「誰在依賴我」)
     */
    const $allCbs = $('.js-perm-cb');
    const reverseMap = {};

    $allCbs.each(function() {
        const myKey = $(this).val();
        const myDepends = $(this).data('depends') || [];
        myDepends.forEach(parentKey => {
            if (!reverseMap[parentKey]) reverseMap[parentKey] = [];
            reverseMap[parentKey].push(myKey);
        });
    });

    /**
     * 核心邏輯：雙向權限聯動
     */
    $allCbs.on('change', function() {
        const isChecked = $(this).is(':checked');
        const currentKey = $(this).val();
        const depends = $(this).data('depends') || [];

        if (isChecked) {
            // 【勾進階補基礎】：勾選刪除，自動勾檢視
            depends.forEach(parentKey => {
                const $parent = $(`.js-perm-cb[value="${parentKey}"]`);
                if (!$parent.is(':checked')) {
                    $parent.prop('checked', true).trigger('change');
                }
            });
        } else {
            // 【放基礎掉進階】：取消檢視，自動取消刪除/編輯
            const children = reverseMap[currentKey] || [];
            children.forEach(childKey => {
                const $child = $(`.js-perm-cb[value="${childKey}"]`);
                if ($child.is(':checked')) {
                    $child.prop('checked', false).trigger('change');
                }
            });
        }
    });

    /**
     * 輔助 UI：全選 / 區域全選
     */
    $('.js-bulk-check').on('click', function() {
        const checkAll = $(this).data('mode') === 'all';
        $allCbs.prop('checked', checkAll).trigger('change');
        $('.js-group-select').prop('checked', checkAll);
    });

    $('.js-group-select').on('change', function() {
        const isChecked = $(this).is(':checked');
        $(`.${$(this).data('target')} .js-perm-cb`).prop('checked', isChecked).trigger('change');
    });
});
</script>
@stop
