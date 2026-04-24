<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AdminRole, User};
use Illuminate\Support\Facades\{DB, Log, Auth};
use App\Helpers\{ContentHelper};

class AdminRoleController extends BaseAdminController
{
    // 權限代碼與頁面標題配置
    protected $permissionName = 'roles';

    /**
     * 角色列表頁
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 取得統一分頁筆數 (自動記憶使用者選 10 筆或 50 筆)
        $perPage = $this->getPerPage($request);

        // 加上 withCount 可以在列表直接顯示「該角色有多少人」，增加實用性
        $roles = AdminRole::withCount('admins')
            ->orderByDesc('id')
            ->paginate($perPage);

        return $this->view('admin.roles.index', compact('roles'));
    }

    /**
     * 新增角色頁面
     */
    public function create()
    {
        return $this->renderForm(new AdminRole());
    }

    /**
     * 編輯角色頁面
     * @param AdminRole $role Laravel Route Model Binding 自動找 ID
     */
    public function edit(AdminRole $role)
    {
        return $this->renderForm($role);
    }

    /**
     * 執行儲存動作 (新增)
     */
    public function store(Request $request)
    {
        return $this->processSave($request, new AdminRole());
    }

    /**
     * 執行更新動作 (編輯)
     */
    public function update(Request $request, AdminRole $role)
    {
        return $this->processSave($request, $role);
    }

    /**
     * 刪除角色
     * @param AdminRole $role
     */
    public function destroy(AdminRole $role)
    {
        // 防呆 1：如果有管理員正屬於這個角色，禁止刪除
        if ($role->admins()->exists()) {
            return back()->with('error', '該角色尚有管理員使用中，請先更改人員角色後再刪除');
        }

        // 防呆 2：保護機制，非開發者不能刪除「超級管理員」角色
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser->isDeveloper() && $role->isSuperRole()) {
            return back()->with('error', '系統保護：您沒有權限刪除超級管理員角色');
        }

        $roleName = $role->name;
        $role->delete();

        // 寫入日誌
        $role->writeLog('刪除', $roleName);

        return back()->with('form_success_swal', '角色已成功刪除');
    }

    /* --- 內部核心邏輯 --- */

    /**
     * 統一處理表單顯示所需的資料轉換
     * 這裡將 backend_permissions.php 的三層結構轉為前端好渲染的格式
     * @param AdminRole $role
     */
    private function renderForm(AdminRole $role)
    {
        $isEdit = $role->exists;
        $rawConfig = config('backend_permissions', []);
        $processedPermissions = [];

        // 權限結構轉換邏輯：群組 -> 子模組 -> 動作
        foreach ($rawConfig as $groupKey => $group) {
            $subs = [];
            foreach ($group['subs'] as $subKey => $sub) {
                $actions = [];
                foreach ($sub['actions'] as $actKey => $actLabel) {
                    $permKey = "{$subKey}.{$actKey}";

                    // 取得此動作的依賴關係 (例如勾選「編輯」必須自動勾選「瀏覽」)
                    $deps = isset($sub['dependencies'][$actKey])
                        ? array_map(fn($d) => "{$subKey}.{$d}", $sub['dependencies'][$actKey])
                        : [];

                    $actions[] = [
                        'key'     => $permKey,
                        'label'   => $actLabel,
                        'depends' => json_encode($deps) // 讓前端 JS 讀取依賴關係
                    ];
                }

                $subs[$subKey] = [
                    'label'   => $sub['label'],
                    'actions' => $actions
                ];
            }

            $processedPermissions[$groupKey] = [
                'label' => $group['label'],
                'subs'  => $subs
            ];
        }

        return $this->view('admin.roles.form', [
            'role'        => $role,
            'isEdit'      => $isEdit,
            'permissions' => $processedPermissions,
        ]);
    }

    /**
     * 統一處理新增與更新的存檔邏輯
     * @param Request $request
     * @param AdminRole $role
     */
    private function processSave(Request $request, AdminRole $role)
    {
        // 1. 基礎驗證
        $request->validate([
            'name' => 'required|max:50|unique:admin_roles,name,' . ($role->id ?? 'NULL'),
        ], [
            'name.required' => '請輸入角色名稱',
            'name.unique'   => '此角色名稱已存在',
        ]);

        return DB::transaction(function () use ($request, $role) {
            try {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                $submittedPerms = $request->input('permissions', []);

                // 2. 安全過濾：非開發者禁止操控 'system.' 開頭的核心權限
                if (!$currentUser->isDeveloper()) {
                    // 如果原本就有 system 權限（例如編輯現有角色），則保留原本的，過濾掉新提交的
                    $originalSystemPerms = array_filter($role->permissions ?? [], fn($p) => str_starts_with($p, 'system.'));
                    $newPerms = array_filter($submittedPerms, fn($p) => !str_starts_with($p, 'system.'));
                    $submittedPerms = array_merge($originalSystemPerms, $newPerms);
                }

                // 3. 後端權限補齊 (防呆核心)
                // 即使使用者繞過前端 JS 勾選，存檔前我們依然根據 Config 強制把必要的依賴權限補進去
                $role->permissions = $this->securePermissions($submittedPerms);

                // 4. 基本資料填充
                $role->name = $request->name;
                $role->description = $request->description;
                $role->save();

                // 5. 紀錄 Log
                $action = $role->wasRecentlyCreated ? '新增' : '編輯';
                $role->writeLog($action, $role->name);

                // 6. 成功回應
                $this->showMsg(0, "角色{$action}成功", [
                    ['text' => '返回列表', 'href' => route('admin.roles.index')],
                    ['text' => '繼續編輯', 'href' => route('admin.roles.edit', $role->id)],
                ]);

                return redirect()->back();

            } catch (\Exception $e) {
                Log::error("Role Save Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '儲存失敗，請聯繫系統開發人員');
            }
        });
    }

    /**
     * 安全檢查：自動補齊權限依賴
     * 例如：若勾選了 'news.edit'，此方法會自動確保 'news.view' 也被存入
     * @param array $perms 原始提交的權限陣列
     * @return array 補齊後的權限陣列
     */
    private function securePermissions(array $perms): array
    {
        $rawConfig = config('backend_permissions', []);
        $finalPerms = $perms;

        foreach ($rawConfig as $group) {
            foreach ($group['subs'] as $subKey => $sub) {
                if (!isset($sub['dependencies'])) continue;

                foreach ($sub['dependencies'] as $actKey => $deps) {
                    $currentKey = "{$subKey}.{$actKey}";

                    // 如果勾選了進階權限，就幫他補上所有關聯的基礎權限
                    if (in_array($currentKey, $finalPerms)) {
                        foreach ($deps as $d) {
                            $finalPerms[] = "{$subKey}.{$d}";
                        }
                    }
                }
            }
        }

        return array_values(array_unique($finalPerms));
    }
}
