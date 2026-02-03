
                        <div class="permission-wrapper">
                            @foreach ($permissions as $modKey => $mod)
                                {{-- 外層卡片：大選單分類 --}}
                                <div class="card card-outline card-secondary mb-4 shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title font-weight-bold">{{ $mod['label'] }}</h3>
                                        <div class="card-tools">
                                            <div class="custom-control custom-checkbox">
                                                {{-- 群組全選按鈕 --}}
                                                <input type="checkbox" class="custom-control-input js-group-select" id="group_{{ $modKey }}" data-target="group-{{ $modKey }}">
                                                <label class="custom-control-label font-weight-normal" for="group_{{ $modKey }}">全選本區</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 內層表格：子選單與功能項目 --}}
                                    <div class="card-body p-0 group-{{ $modKey }}">
                                        <table class="table table-hover mb-0">
                                            <tbody>
                                                @foreach ($mod['subs'] as $subKey => $sub)
                                                    <tr>
                                                        {{-- 子選單名稱：使用自定義 Class 移除 bg-light 並維持質感 --}}
                                                        <td width="200" class="permission-sub-label border-right">
                                                            {{ $sub['label'] }}
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap">
                                                                @foreach ($sub['actions'] as $action)
                                                                    <div class="custom-control custom-checkbox mr-4 mb-2">
                                                                        <input type="checkbox"
                                                                            name="permissions[]"
                                                                            value="{{ $action['key'] }}"
                                                                            id="perm_{{ str_replace('.', '_', $action['key']) }}"
                                                                            class="custom-control-input perm-checkbox"
                                                                            data-depends="{{ json_encode($action['depends']) }}"
                                                                            {{ $action['checked'] ? 'checked' : '' }}>

                                                                        <label class="custom-control-label font-weight-normal" for="perm_{{ str_replace('.', '_', $action['key']) }}">
                                                                            {{ $action['label'] }}
                                                                            {{-- 角色已有標籤 --}}
                                                                            <span class="role-owned-badge badge badge-light border ml-1 text-secondary" style="display:none;">角色已有</span>
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
