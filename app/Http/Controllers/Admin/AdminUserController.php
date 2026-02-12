<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Admin, AdminRole};
use Illuminate\Support\Facades\{DB, Hash, Auth, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class AdminUserController extends BaseAdminController
{
    // 定義權限模組名稱，用於 Middleware 自動檢查 (除了 edit/update 我們會手動處理例外)
    protected $permissionName = 'admins';
    protected $pageTitle = '網站管理員';

    /**
     * 頁面相關配置
     * 統一管理檔案上傳設定，比照最新消息架構，好調整參數
     */
    protected $pageCfg = [
        'files' => [
            'avatar_url' => [
                'path'   => 'admins',         // 儲存路徑
                'width'  => 600,             // 寬度
                'height' => 400,             // 高度
                'mode'   => 'center_crop',   // 處理模式
                'useOriginalName' => false,  // 自動生成唯一名稱
            ],
        ],
    ];

    /**
     * 列表頁：顯示管理員清單
     */
    public function index(Request $request)
    {
        /** @var Admin $currentUser */
        $currentUser = Auth::user();

        // 使用 with 預先載入關聯資料，避免 N+1 問題 (提升效能)
        $query = Admin::with(['role', 'childrenRecursive.role']);

        // 權限分級顯示邏輯：開發者看全部，其餘看下屬或自己
        if (!$currentUser->isDeveloper()) {
            if ($currentUser->isInternalAdmin()) {
                // 內部管理員：抓出頂層非開發者帳號
                $query->whereNull('parent_id')
                    ->whereDoesntHave('role', function ($q) {
                        // 保留原有過濾邏輯
                    });
            } else {
                // 一般管理員：只能看到自己與延伸出的下屬
                $query->where('id', $currentUser->id);
            }
        } else {
            // 開發者：看所有根節點
            $query->whereNull('parent_id');
        }

        $admins = $query->get();

        return $this->view('admin.admins.index', compact('admins'));
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $admin = new Admin();
        // 預設給予一些基礎設定值，避免前端 JS 報錯
        $admin->preferences = [
            'dark_mode' => true,
            'sidebar_collapse' => false,
            'theme_color' => 'default'
        ];

        return $this->renderForm($admin, false);
    }

    /**
     * 儲存新增資料
     */
    public function store(Request $request)
    {
        // 驗證表單資料
        $this->validateRequest($request, false);

        /** @var Admin $currentUser */
        $currentUser = Auth::user();

        return DB::transaction(function () use ($request, $currentUser) {
            try {
                $admin = new Admin();

                // 處理頭像檔案上傳 (套用萬用檔案處理邏輯)
                $this->handleFileUploads($request, $admin);

                // 排除不直接儲存的欄位，保持資料乾淨
                $data = $request->except(['password', 'avatar_url', 'permissions', 'preferences']);

                // 密碼加密與狀態設定
                $data['password']  = Hash::make($request->password);
                $data['is_active'] = $request->has('is_active');

                // 處理權限核心邏輯
                $data['permissions'] = $this->securePermissions($request->input('permissions', []));

                // 自動設定帳號所屬上層
                if (!$currentUser->isDeveloper() && !$currentUser->isInternalAdmin()) {
                    $data['parent_id'] = $currentUser->id;
                }

                $admin->fill($data)->save();

                // 紀錄操作日誌
                if (method_exists($admin, 'writeLog')) {
                    $admin->writeLog('新增', "建立管理員：{$admin->name}");
                }

                ContentHelper::showMsg(0, '新增完成', [
                    ['text' => '返回列表', 'href' => route('admin.admins.index')],
                ], true);

                return redirect()->route('admin.admins.index');
            } catch (\Exception $e) {
                Log::error("AdminUser Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗');
            }
        });
    }

    /**
     * 個人資料入口 (避開 Middleware 權限檢查的核心跳板)
     */
    public function profile()
    {
        /** @var Admin $admin */
        $admin = Auth::user();

        if (!$admin) {
            abort(403, '未經授權訪問');
        }

        // 直接調用編輯方法，傳入自己的 ID 並註記這是 profile 路由
        return $this->edit($admin->id, true);
    }

    /**
     * 編輯頁：支援「編輯自己」與「編輯他人」
     * 參數保持 $id 以免干涉現有 Middleware 邏輯
     */
    public function edit($id, $isProfileRoute = false)
    {
        $admin = Admin::findOrFail($id);

        /** @var Admin $currentUser */
        $currentUser = Auth::user();

        // 判斷當前操作是否為「修改本人」
        $isSelf = ($admin->id === $currentUser->id);

        // 安全防呆：如果走 profile 路由但查出來的不是自己則擋掉
        if ($isProfileRoute && !$isSelf) {
            abort(403);
        }

        // 權限檢查：不是本人且沒有編輯權限 -> 擋掉
        if (!$isSelf && !$currentUser->canDo('admins.edit')) {
            abort(403, '您沒有權限編輯此管理員。');
        }

        return $this->renderForm($admin, $isSelf, $isProfileRoute);
    }

    /**
     * 個人資料儲存跳板 (避開通用 CRUD 權限檢查)
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::user();
        return $this->update($request, $admin->id);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $isProfileRoute = ($request->route()->named('admin.admins.updateProfile'));

        /** @var Admin $currentUser */
        $currentUser = Auth::user();
        $isSelf = ($admin->id === $currentUser->id);

        // 權限防線：非本人且非 Profile 路由且無權限時攔截
        if (!$isProfileRoute && !$isSelf && !$currentUser->canDo('admins.edit')) {
            abort(403, '您沒有權限執行此操作。');
        }

        // 驗證請求資料
        $this->validateRequest($request, true);

        return DB::transaction(function () use ($request, $admin, $isSelf, $isProfileRoute, $currentUser) {
            try {
                // 處理檔案/圖片更新 (自動判斷舊檔並刪除)
                $this->handleFileUploads($request, $admin);

                // 排除非直接更新的欄位與介面偏好 input
                $data = $request->except([
                    'password', 'permissions', 'role_id', 'is_active', 'avatar_url', 'preferences', 'parent_id',
                    'pref_dark_mode', 'pref_sidebar_collapse', 'pref_nav_flat', 'pref_sidebar_fixed',
                    'pref_text_sm', 'pref_navbar_variant', 'pref_sidebar_variant', 'pref_accent_color', 'pref_brand_variant'
                ]);

                // 密碼變更處理
                if ($request->filled('password')) {
                    $data['password'] = Hash::make($request->password);
                }

                // 權限設定 (僅限「非本人」修改時生效，增加安全性)
                if (!$isSelf) {
                    $data['is_active']   = $request->has('is_active');
                    $data['role_id']     = $request->input('role_id');
                    $data['parent_id']   = $request->input('parent_id');
                    $data['permissions'] = $this->securePermissions($request->input('permissions', []));
                }

                // 個人化介面設定處理 (僅限「本人」修改時生效)
                if ($isSelf) {
                    $currentPrefs = $admin->preferences ?? [];
                    $newPrefs = [
                        'dark_mode'        => $request->has('pref_dark_mode'),
                        'sidebar_collapse' => $request->has('pref_sidebar_collapse'),
                        'nav_flat'         => $request->has('pref_nav_flat'),
                        'sidebar_fixed'    => $request->has('pref_sidebar_fixed'),
                        'text_sm'          => $request->has('pref_text_sm'),
                        'navbar_color'     => $request->input('pref_navbar_variant'),
                        'sidebar_theme'    => $request->input('pref_sidebar_variant'),
                        'accent_color'     => $request->input('pref_accent_color'),
                        'brand_color'      => $request->input('pref_brand_variant'),
                    ];
                    // 過濾掉 null 並與現有設定合併
                    $data['preferences'] = array_merge($currentPrefs, array_filter($newPrefs, fn($v) => !is_null($v)));
                }

                $admin->update($data);

                // 紀錄操作日誌
                if (method_exists($admin, 'writeLog')) {
                    $admin->writeLog('編輯', "更新帳號：{$admin->name}");
                }

                // 完成後的導向按鈕邏輯
                $buttons = [
                    ['text' => '繼續編輯', 'href' => $isSelf ? route('admin.admins.profile') : route('admin.admins.edit', $admin->id)],
                ];

                if (!($isProfileRoute && $isSelf && $currentUser->canDo('admins.edit'))) {
                    $buttons[] = ['text' => '返回列表', 'href' => route('admin.admins.index')];
                }

                ContentHelper::showMsg(0, '資料更新完成', $buttons, true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("AdminUser Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    /**
     * 刪除管理員
     * 使用 Route Model Binding 直接注入 Admin 物件
     */
    public function destroy(Admin $admin)
    {
        /** @var Admin $currentUser */
        $currentUser = Auth::user();

        // 判斷當前操作是否為「修改本人」
        $isSelf = ($admin->id === $currentUser->id);

        // 【安全性防呆】絕對不能刪除目前登入的自己
        if ($isSelf) {
            return back()->with('form_error_swal', '操作失敗：您無法刪除目前的登入帳號。');
        }

        //【業務邏輯防呆】檢查是否有子帳號 (下屬)
        // 這是為了確保樹狀結構的完整性，避免產生孤兒資料
        if ($admin->children()->exists()) {
            return back()->with('form_error_swal', '操作失敗：該帳號下仍有管理員，請先處理下屬。');
        }

        //【資源清理】刪除頭像圖檔 (參考 ImageHelper 寫法)
        // 防呆：避免資料庫刪了，但硬碟留下一堆垃圾圖檔
        if ($admin->avatar_url) {
            // 假設你的管理員頭像欄位叫 avatar_url，磁碟區是 public
            ImageHelper::deleteImage($admin->avatar_url, 'public');
        }

        //【關聯清理】如果管理員有額外的權限紀錄或其他關聯資料，要在這裡先處理
        // 例如：$admin->permissions()->delete();

        //【執行刪除】
        $adminName = $admin->name; // 先存起來，等下 Log 要用
        $admin->delete();

        //【紀錄軌跡】專業系統必備：誰在什麼時候刪除了誰
        // 假設你的 Admin Model 也有寫 writeLog 方法
        $admin->writeLog('刪除', "管理員帳號: {$adminName}");

        //【回傳結果】統一使用 swal 提示增加使用者體驗
        return redirect()->route('admin.admins.index')->with('form_success_swal', '管理員帳號已移除');
    }

    /* --- 內部輔助方法 (Private Helper Methods) --- */

    /**
     * 渲染表單通用邏輯 (萃取重複代碼)
     */
    private function renderForm(Admin $admin, $isSelf, $fromProfile = false)
    {
        $isEdit = $admin->exists;

        // 防呆：避免將子孫設定為自己的上層，造成樹狀結構死循環
        $descendantIds = $this->getDescendantIds($admin);
        $descendantIds[] = $admin->id;
        $parents = Admin::whereNotIn('id', $descendantIds)->get();

        // 整理個人偏好設定與預設值合併
        $adminPrefs = array_merge([
            'dark_mode'        => true,
            'sidebar_collapse' => false,
            'navbar_color'     => 'navbar-white navbar-light',
            'sidebar_theme'    => 'sidebar-dark-primary',
        ], $admin->preferences ?? []);

        return $this->view('admin.admins.form', [
            'admin'            => $admin,
            'roles'           => AdminRole::all(),
            'parents'         => $parents,
            'isEdit'          => $isEdit,
            'pageTitle'       => $isSelf ? '個人資料設定' : ($isEdit ? '編輯管理員' : '新增管理員'),
            'permissions'     => $this->preparePermissions($admin),
            'adminPrefs'       => $adminPrefs,
            'showPermissions' => !$isSelf,
            'showPersonal'    => $isSelf,
            'isSelf'          => $isSelf,
            'fromProfile'     => $fromProfile,
            'fileConfigs'     => $this->pageCfg['files'], // 傳遞尺寸設定給前端顯示提示
        ]);
    }

    /**
     * 驗證請求邏輯
     * 包含防呆處理，確保在更新時能正確排除掉自己的 Email 檢查
     */
    private function validateRequest(Request $request, $isUpdate)
    {
        // 【防呆邏輯】精準取得當前要排除的 Admin ID
        // 優先順序：路由參數(admin/id) > 網址直接傳入的參數 > 當前登入者 ID (針對 profile 修改)
        $adminId = $request->route('admin')
                  ?? $request->route('id')
                  ?? $request->id
                  ?? ($request->route()->named('admin.admins.updateProfile') ? Auth::id() : null);

        $rules = [
            'name'  => 'required|string|max:100',
            // unique 規則：資料表, 欄位, 要排除的 ID
            'email' => 'required|email|unique:admins,email,' . ($adminId ?: 'NULL'),
        ];

        // 根據「新增」或「更新」切換密碼必填狀態
        if ($isUpdate) {
            // 更新時：沒填密碼代表不修改
            $rules['password'] = 'nullable|confirmed|min:6';
        } else {
            // 新增時：密碼與角色必填
            $rules['password'] = 'required|confirmed|min:6';
            $rules['role_id']  = 'required';
        }

        // 執行驗證，並自定義友善的錯誤訊息
        return $request->validate($rules, [
            'name.required'      => '請填寫管理員姓名',
            'email.required'     => '請填寫 Email 帳號',
            'email.unique'       => '此 Email 帳號已被其他管理員使用',
            'password.required'  => '請設定登入密碼',
            'password.confirmed' => '兩次輸入的密碼不一致',
            'password.min'       => '密碼長度至少需要 6 個字',
            'role_id.required'   => '請選擇管理員角色',
        ]);
    }

    /**
     * 萬用檔案上傳處理邏輯 (與最新消息架構完全一致)
     */
    private function handleFileUploads(Request $request, Admin $admin)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {
                $admin->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $admin->$field, // 傳入舊檔路徑供自動清理
                    $config
                );
            }
        }
    }

    /**
     * 整理權限顯示結構
     */
    private function preparePermissions($admin)
    {
        $rawConfig = config('backend_permissions') ?? [];
        $processed = [];
        foreach ($rawConfig as $groupKey => $group) {
            $subs = [];
            foreach ($group['subs'] as $subKey => $sub) {
                $actions = [];
                foreach ($sub['actions'] as $actKey => $actLabel) {
                    $permKey = "{$subKey}.{$actKey}";
                    $actions[] = [
                        'key'     => $permKey,
                        'label'   => $actLabel,
                        'checked' => in_array($permKey, $admin->permissions ?? []),
                        'depends' => isset($sub['dependencies'][$actKey])
                            ? array_map(fn($d) => "{$subKey}.{$d}", $sub['dependencies'][$actKey])
                            : []
                    ];
                }
                $subs[$subKey] = ['label' => $sub['label'], 'actions' => $actions];
            }
            $processed[$groupKey] = ['label' => $group['label'], 'subs' => $subs];
        }
        return $processed;
    }

    /**
     * 遞迴取得所有子帳號 ID
     */
    private function getDescendantIds(Admin $admin): array
    {
        $ids = [];
        foreach ($admin->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * 確保權限資料格式正確
     */
    private function securePermissions($submittedPerms)
    {
        return array_unique((array)$submittedPerms);
    }
}
