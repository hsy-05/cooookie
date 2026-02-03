@extends('adminlte::page')

@section('title', $pageTitle)

@section('content_header')
    <h1>{{ $pageTitle }}</h1>
@stop

@section('content')
<x-admin.page-message>

    <form method="POST" enctype="multipart/form-data"
          action="{{ ($isSelf && isset($fromProfile)) ? route('admin.users.updateProfile') : ($isEdit ? route('admin.users.update', $user->id) : route('admin.users.store')) }}">
        @csrf
        {{-- 如果是 updateProfile，也是用 PUT --}}
        @if($isEdit || ($isSelf && isset($fromProfile))) @method('PUT') @endif

        <div class="row">
            {{-- 左側：主要表單 --}}
            <div class="col-md-12">
                <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs custom-styled-tabs" id="custom-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-general-link" data-toggle="pill" href="#tab-general" role="tab">
                                    <i class="fas fa-user mr-1"></i> 基本資料
                                </a>
                            </li>

                            {{-- 2. 權限控管 (只有編輯他人時顯示) --}}
                            @if($showPermissions && !$isSelf)
                            <li class="nav-item">
                                <a class="nav-link" id="tab-permission-link" data-toggle="pill" href="#tab-permission" role="tab">
                                    <i class="fas fa-user-shield mr-1"></i> 權限與角色
                                </a>
                            </li>
                            @endif

                            {{-- 只有編輯自己時才顯示「介面設定」分頁 --}}
                            @if($showPersonal)
                            <li class="nav-item">
                                <a class="nav-link" id="tab-style-link" data-toggle="pill" href="#tab-style" role="tab">
                                    <i class="fas fa-palette mr-1"></i> 介面風格設定
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-content">

                            {{-- 一般資料標籤內容 --}}
<div class="tab-pane fade show active" id="tab-general" role="tabpanel">
    <div class="row">
        <div class="col-md-8">
            {{-- 姓名與帳號 --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="name">真實姓名 <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="email">登入帳號 (Email) <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ $user->email }}" required {{ $isEdit ? 'readonly' : '' }}>
                </div>
            </div>

            {{-- 密碼區塊：移除內嵌 Style，改用 Bootstrap 類別 --}}
            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label for="password">
                        密碼 {{ !$isEdit ? '*' : '(若不修改請留空)' }}
                    </label>
                    <input type="password" id="password" name="password" class="form-control" {{ !$isEdit ? 'required' : '' }}>
                </div>
                <div class="col-md-6 form-group">
                    <label for="password_confirmation">確認密碼</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    {{-- 使用 CSS 類別 d-none-soft 取代 style="display:none" --}}
                    <small id="password-error" class="text-danger font-weight-bold d-none-soft">
                        <i class="fas fa-exclamation-circle"></i> 密碼輸入不一致
                    </small>
                </div>
            </div>

            {{-- 管理員歸屬設定 --}}
            @if($showPermissions)
            <div class="row bg-light p-3 rounded mt-4 border mx-0">
                <div class="col-12 mb-2">
                    <label class="text-muted"><i class="fas fa-cog"></i> 管理員歸屬設定</label>
                </div>
                <div class="col-md-6 form-group">
                    <label>所屬角色</label>
                    <select name="role_id" class="form-control" required id="role_select">
                        <option value="">請選擇</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ $user->role_id == $role->id ? 'selected' : '' }}
                                data-permissions='{{ json_encode($role->permissions ?? []) }}'>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>上層主管</label>
                    <select name="parent_id" class="form-control">
                        <option value="">-- 設定為頂層 --</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" {{ $user->parent_id == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $user->is_active || !$isEdit ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">啟用此帳號</label>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- 右側：頭像上傳 （200x200） --}}
        <div class="col-md-4 text-center">
            <label>個人頭像</label>
            <div class="mb-3">
                <img src="{{ $user->avatar_url ? asset('storage/'.$user->avatar_url) : asset('images/admin/default-avatar.png') }}"
                     class="profile-user-img img-fluid img-circle elevation-2 avatar-preview"
                     alt="User Avatar">
            </div>
            <div class="custom-file">
                <input type="file" name="avatar_url" class="custom-file-input" id="avatar_url">
                <label class="custom-file-label" for="avatar_url">更換圖片...</label>
            </div>
        </div>
    </div>
</div>

                            {{-- 權限設定內容 --}}
                            @if($showPermissions && !$isSelf)
                            <div class="tab-pane fade" id="tab-permission" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    灰色勾選項目為「角色既有權限」，不可修改。您可在此為該用戶開放額外特例。
                                </div>
                                {{-- 這裡放置您的權限 Loop 程式碼 (與原檔相同，略過以節省篇幅) --}}
                                {{-- 請直接複製原檔 Permission Wrapper 內的 foreach --}}
                                @include('admin.users._permission_list') {{-- 建議將那一大串抽離成 partial view --}}
                            </div>
                            @endif

                            {{-- 介面風格 (Live Preview) --}}
                            @if($showPersonal)
<div class="tab-pane fade" id="tab-style" role="tabpanel">
    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-magic mr-2"></i><b>即時預覽：</b>您可以隨意調整，按下儲存後才會正式更新。
    </div>

    {{-- 1. 快速主題 --}}
    <div class="form-group">
        <div class="style-divider text-primary"><i class="fas fa-bolt mr-1"></i> 快速主題 (Designer Presets)</div>
        <div class="d-flex flex-wrap align-items-center">
            <div class="btn-group-toggle mr-2" data-toggle="buttons">
                <label class="btn btn-outline-dark mb-2 mr-1"><input type="radio" name="theme_preset" value="cyber"> 科技極夜</label>
                <label class="btn btn-outline-olive mb-2 mr-1"><input type="radio" name="theme_preset" value="olive"> 橄欖綠</label>
                <label class="btn btn-outline-navy mb-2 mr-1"><input type="radio" name="theme_preset" value="navy"> 海軍藍</label>
                <label class="btn btn-outline-purple mb-2 mr-1"><input type="radio" name="theme_preset" value="purple"> 皇家紫</label>
                <label class="btn btn-outline-warning mb-2 mr-1"><input type="radio" name="theme_preset" value="classic"> 經典暖陽</label>
                <label class="btn btn-outline-pink mb-2 mr-1"><input type="radio" name="theme_preset" value="pink"> 玫紅色</label>
                <label class="btn btn-outline-success mb-2 mr-1"><input type="radio" name="theme_preset" value="forest"> 森林靜謐</label>
                <label class="btn btn-outline-info mb-2 mr-1"><input type="radio" name="theme_preset" value="ocean"> 海洋深潛</label>
            </div>
            <button type="button" class="btn btn-default btn-sm mb-2" id="js-reset-theme"><i class="fas fa-undo-alt text-danger"></i> 還原</button>
        </div>
    </div>

    <div class="row">
        {{-- 2. 功能切換 --}}
        <div class="col-md-5">
            <div class="style-divider">介面功能</div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="pref_dark_mode" name="pref_dark_mode" @checked($userPrefs['dark_mode'] ?? false)>
                <label class="custom-control-label font-weight-normal" for="pref_dark_mode">深色模式</label>
            </div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="pref_sidebar_collapse" name="pref_sidebar_collapse" @checked($userPrefs['sidebar_collapse'] ?? false)>
                <label class="custom-control-label font-weight-normal" for="pref_sidebar_collapse">自動收合選單</label>
            </div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="pref_nav_flat" name="pref_nav_flat" @checked($userPrefs['nav_flat'] ?? false)>
                <label class="custom-control-label font-weight-normal" for="pref_nav_flat">扁平化樣式</label>
            </div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="pref_sidebar_fixed" name="pref_sidebar_fixed" @checked($userPrefs['sidebar_fixed'] ?? false)>
                <label class="custom-control-label font-weight-normal" for="pref_sidebar_fixed">固定側邊欄</label>
            </div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="pref_text_sm" name="pref_text_sm" @checked($userPrefs['text_sm'] ?? false)>
                <label class="custom-control-label font-weight-normal" for="pref_text_sm">緊湊文字 (Text-SM)</label>
            </div>
        </div>

        {{-- 3. 顏色細節 (各 6 種精選) --}}
        <div class="col-md-7 border-left">
            <div class="style-divider">色彩配置</div>

            <div class="form-group row mb-2">
                <label class="col-sm-5 col-form-label-sm">導覽列樣式 (Navbar)</label>
                <div class="col-sm-7">
                    <select name="pref_navbar_variant" id="pref_navbar_variant" class="form-control form-control-sm">
                        <optgroup label="基礎色">
                            <option value="navbar-white navbar-light" {{ ($userPrefs['navbar_color'] == 'navbar-white navbar-light') ? 'selected' : '' }}>淺色 (White)</option>
                            <option value="navbar-dark navbar-dark" {{ ($userPrefs['navbar_color'] == 'navbar-dark navbar-dark') ? 'selected' : '' }}>純深色 (Dark)</option>
                        </optgroup>
                        <optgroup label="品牌色">
                            <option value="navbar-dark navbar-olive" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-olive')>橄欖綠</option>
                            <option value="navbar-dark navbar-navy" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-navy')>海軍深藍</option>
                            <option value="navbar-dark navbar-purple" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-purple')>皇家紫</option>
                            <option value="navbar-light navbar-warning" @selected($userPrefs['navbar_color'] == 'navbar-light navbar-warning')>經典暖陽</option>
                            <option value="navbar-dark navbar-pink" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-pink')>玫紅色</option>
                            <option value="navbar-dark navbar-success" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-success')>森林靜謐</option>
                            <option value="navbar-dark navbar-info" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-info')>海洋藍</option>
                            <option value="navbar-dark navbar-primary" @selected($userPrefs['navbar_color'] == 'navbar-dark navbar-primary')>科技藍</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="form-group row mb-2">
                <label class="col-sm-5 col-form-label-sm">側邊欄樣式 (Sidebar)</label>
                <div class="col-sm-7">
                    <select name="pref_sidebar_variant" id="pref_sidebar_variant" class="form-control form-control-sm">
                        <option value="sidebar-dark-purple" @selected($userPrefs['sidebar_theme'] == 'sidebar-dark-purple')>深色 - 皇家紫</option>
                        <option value="sidebar-dark-warning" @selected($userPrefs['sidebar_theme'] == 'sidebar-dark-warning')>深色 - 經典暖陽</option>
                        <option value="sidebar-dark-pink" @selected($userPrefs['sidebar_theme'] == 'sidebar-dark-pink')>深色 - 玫紅色</option>
                        <option value="sidebar-dark-olive" {{ ($userPrefs['sidebar_theme'] == 'sidebar-dark-olive') ? 'selected' : '' }}>深色 - 橄欖綠</option>
                        <option value="sidebar-dark-success" @selected($userPrefs['sidebar_theme'] == 'sidebar-dark-success')>深色 - 森林靜謐</option>
                        <option value="sidebar-dark-primary" @selected($userPrefs['sidebar_theme'] == 'sidebar-dark-primary')>深色 - 簡約藍</option>
                        <option value="sidebar-light-primary" @selected($userPrefs['sidebar_theme'] == 'sidebar-light-primary')>淺色 - 簡約藍</option>
                        <option value="sidebar-light-navy" @selected($userPrefs['sidebar_theme'] == 'sidebar-light-navy')>淺色 - 海軍藍</option>
                        <option value="sidebar-light-info" @selected($userPrefs['sidebar_theme'] == 'sidebar-light-info')>淺色 - 海洋藍</option>
                    </select>
                </div>
            </div>

            <div class="form-group row mb-2">
                <label class="col-sm-5 col-form-label-sm">強調色 (Accent)</label>
                <div class="col-sm-7">
                    <select name="pref_accent_color" id="pref_accent_color" class="form-control form-control-sm">
                        <option value="" @selected(empty($userPrefs['accent_color']))>無</option>
                        <option value="accent-primary" @selected($userPrefs['accent_color'] == 'accent-primary')>簡約藍 (Primary)</option>
                        <option value="accent-success" @selected($userPrefs['accent_color'] == 'accent-success')>森林靜謐 (Success)</option>
                        <option value="accent-navy" @selected($userPrefs['accent_color'] == 'accent-navy')>海軍藍 (Navy)</option>
                        <option value="accent-warning" @selected($userPrefs['accent_color'] == 'accent-warning')>經典暖陽 (Warning)</option>
                        <option value="accent-danger" @selected($userPrefs['accent_color'] == 'accent-danger')>烈焰紅 (Danger)</option>
                        <option value="accent-purple" @selected($userPrefs['accent_color'] == 'accent-purple')>皇家紫 (purple)</option>
                        <option value="accent-pink" @selected($userPrefs['accent_color'] == 'accent-pink')>玫紅色 (Pink)</option>
                        <option value="accent-olive" @selected($userPrefs['accent_color'] == 'accent-olive')>橄欖綠 (Olive)</option>
                        <option value="accent-info" @selected($userPrefs['accent_color'] == 'accent-info')>海洋藍 (Info)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
                        </div>
                    </div>

                    <div class="card-footer text-center">
                        @if(isset($fromProfile) && $fromProfile)
                            {{-- 從 Profile 來，不需要「返回列表」 --}}
                        @else
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">返回列表</a>
                        @endif

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> 儲存設定
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</x-admin.page-message>
@stop

@section('js')
<script>
$(function() {
    /**
     * 密碼防呆驗證 (使用 SweetAlert2)
     */
    $('form').on('submit', function(e) {
        const pass = $('#password').val();
        const confirm = $('#password_confirmation').val();

        // 邏輯：如果有輸入密碼，才進行一致性檢查
        if (pass !== '' || confirm !== '') {
            if (pass !== confirm) {
                // 1. 阻擋表單送出
                e.preventDefault();

                // 2. 提示窗
                showAlert(
                    "error",         // type
                    "驗證失敗",      // title
                    "兩次密碼輸入不一致，請重新確認。", // message
                    true,            // toast
                    "center"        // position 改為右上角，不遮擋視線
                );

                // 3. 視覺防呆：顯示紅字與紅框
                $('#password_confirmation').addClass('is-invalid-border');
                $('#password-error').removeClass('d-none-soft'); // 移除隱藏類別

                return false;
            }
        }
    });

    /**
     * 即時解除錯誤視覺效果
     * 使用者一旦開始重新輸入，就應該把警告消掉，這是專業 UX 的細節
     */
    $('#password_confirmation, #password').on('input', function() {
        const pass = $('#password').val();
        const confirm = $('#password_confirmation').val();

        // 如果輸入一致了，或是清空了，就隱藏警告
        if (pass === confirm || confirm === '') {
            $('#password_confirmation').removeClass('is-invalid-border');
            $('#password-error').addClass('d-none-soft');
        }
    });

    // 讓上傳檔案顯示檔名
    $('.custom-file-input').on('change', function () {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>

<script>
$(function() {// 只有在顯示權限 Tab 時才執行同步邏輯
    @if($showPermissions)
        const $roleSelect = $('#role_select');
        const $checkboxes = $('.perm-checkbox');

        /**
         * 1. 角色權限同步 (鎖定功能)
         * 說明：當切換角色時，自動勾選並鎖定該角色「已經擁有」的權限。
         */
        function syncRolePermissions() {
            const $selected = $roleSelect.find('option:selected');
            if (!$selected.val()) return;

            // 取得角色資料，確保 permissions 永遠是陣列
            const isSystem = String($selected.data('is-system')) === '1';
            let rolePerms = $selected.data('permissions');

            // 防呆：如果後端噴出來的是 JSON 字串，手動解析它
            if (typeof rolePerms === 'string') {
                try { rolePerms = JSON.parse(rolePerms); } catch(e) { rolePerms = []; }
            }
            if (!Array.isArray(rolePerms)) rolePerms = [];

            $checkboxes.each(function() {
                const $cb = $(this);
                const val = $cb.val();

                // 【修正重點】使用 closest 往上找容器，再往下找標籤，比 siblings 更精準
                const $wrapper = $cb.closest('.custom-control');
                const $badge = $wrapper.find('.role-owned-badge');

                const hasAccess = isSystem || rolePerms.includes(val);

                if (hasAccess) {
                    // 角色已有的：勾選、鎖定(disabled)、顯示標籤
                    $cb.prop('checked', true).prop('disabled', true);
                    $badge.show();
                } else {
                    // 角色沒有的：解除鎖定、隱藏標籤
                    $cb.prop('disabled', false);
                    $badge.hide();

                    // 注意：這裡不強制將 checked 設為 false，因為「額外賦予」的權限應保留
                }
            });
        }

        /**
         * 2. 雙向依賴邏輯
         * 說明：勾選「刪除」自動勾選「檢視」，取消「檢視」自動取消「刪除」。
         */
        $checkboxes.on('change', function() {
            const isChecked = $(this).is(':checked');
            const currentKey = $(this).val();

            if (isChecked) {
                // 自動勾選依賴項
                const deps = $(this).data('depends') || [];
                deps.forEach(key => {
                    const $target = $(`.perm-checkbox[value="${key}"]`);
                    if (!$target.is(':disabled')) {
                        $target.prop('checked', true);
                    }
                });
            } else {
                // 反向連鎖取消
                $checkboxes.each(function() {
                    const otherDeps = $(this).data('depends') || [];
                    if (otherDeps.includes(currentKey)) {
                        $(this).prop('checked', false).trigger('change');
                    }
                });
            }
        });

        /**
         * 3. 全選 / 全取消 (全局按鈕)
         */
        $('.js-bulk-check').on('click', function() {
            const mode = $(this).data('mode');
            // 只操作「沒被鎖定(非角色已有)」的 checkbox
            const $targets = $('.perm-checkbox:not(:disabled)');

            if (mode === 'all') {
                $targets.prop('checked', true).trigger('change');
            } else {
                $targets.prop('checked', false).trigger('change');
            }
        });

        /**
         * 4. 群組全選 (區塊按鈕)
         */
        $('.js-group-select').on('change', function() {
            const targetClass = $(this).data('target');
            const isChecked = $(this).is(':checked');
            $(`.${targetClass} .perm-checkbox:not(:disabled)`).prop('checked', isChecked).trigger('change');
        });

        // 初始化
        $roleSelect.on('change', syncRolePermissions);
        syncRolePermissions();
    @endif
});
</script>
@stop
