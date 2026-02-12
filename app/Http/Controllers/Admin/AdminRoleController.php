<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{User, AdminRole};
use Illuminate\Support\Facades\{DB, Log, Hash, Auth};
use App\Helpers\{ContentHelper};

class AdminRoleController extends BaseAdminController
{
    protected $permissionName = 'roles';
    protected $pageTitle = '管理員角色';

    public function index()
    {
        // 取得所有角色並統計人數，分頁顯示
        $roles = AdminRole::withCount('admins')->paginate(10);
        return $this->view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return $this->prepareForm(new AdminRole(), false, '新增角色');
    }

    public function edit($id)
    {
        $role = AdminRole::findOrFail($id);
        return $this->prepareForm($role, true, '編輯角色');
    }

    /**
     * 內部私有函式：統一處理表單需要的資料
     * 邏輯說明：將 config 裡的兩層結構轉換為前端表格容易渲染的格式
     */
    private function prepareForm($role, $isEdit, $pageTitle)
    {
        $rawConfig = config('backend_permissions'); // 取得新的兩層結構 config
        $processedPermissions = [];

        // 第一層循環：群組群（例如：消息管理、權限設定）
        foreach ($rawConfig as $groupKey => $group) {
            $subs = [];

            // 第二層循環：子模組（例如：最新消息、角色管理）
            foreach ($group['subs'] as $subKey => $sub) {
                $actions = [];

                // 第三層循環：具體動作（例如：view, create, edit）
                foreach ($sub['actions'] as $actKey => $actLabel) {
                    // 組合成子模組.動作，例如 "news.view"
                    $permKey = "{$subKey}.{$actKey}";

                    // 處理依賴關係：如果勾選 A 必須勾選 B，格式化為前端 JS 好處理的陣列
                    $depends = isset($sub['dependencies'][$actKey])
                        ? array_map(fn($d) => "{$subKey}.{$d}", $sub['dependencies'][$actKey])
                        : [];

                    $actions[] = [
                        'key' => $permKey,
                        'label' => $actLabel,
                        'depends' => json_encode($depends) // 轉成 JSON 字串讓前端 data 屬性讀取
                    ];
                }

                $subs[$subKey] = [
                    'label'   => $sub['label'],
                    'actions' => $actions
                ];
            }

            // 組裝回傳結構
            $processedPermissions[$groupKey] = [
                'label' => $group['label'],
                'subs'  => $subs
            ];
        }

        return $this->view('admin.roles.form', [
            'role' => $role,
            'isEdit' => $isEdit,
            'pageTitle' => $pageTitle,
            'permissions' => $processedPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:admin_roles,name']);

        $data = $request->only(['name', 'description']);
        // 通過安全補齊邏輯，確保存入資料庫的權限是完整的
        $data['permissions'] = $this->securePermissions($request->input('permissions', []));

        $role = AdminRole::create($data);

        ContentHelper::showMsg(0, '新增完成', [
            ['text' => '返回列表', 'href' => route('admin.roles.index')],
            ['text' => '繼續編輯', 'href' => route('admin.roles.edit', $role->id)],
        ], true);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        /** @var User $admin */
        $admin = Auth::user();

        $role = AdminRole::findOrFail($id);
        $request->validate(['name' => 'required|unique:admin_roles,name,' . $id]);

        $data = $request->only(['name', 'description']);

        // 防呆：如果不是開發者，過濾掉開頭為 system. 的核心權限（保護系統）
        $submittedPerms = $request->input('permissions', []);
        if (!$admin->isDeveloper()) {
            $submittedPerms = array_filter($submittedPerms, fn($p) => !str_starts_with($p, 'system.'));
        }

        // 依賴補齊：後端再次檢查（防止使用者透過 F12 竄改前端檢查邏輯）
        $data['permissions'] = $this->securePermissions($submittedPerms);

        $role->update($data);
        return redirect()->route('admin.roles.index')->with('success', '角色更新成功');
    }

    public function destroy($id)
    {
        /** @var User $admin */
        $admin = Auth::user();
        $role = AdminRole::findOrFail($id);

        if ($role->admins()->exists()) {
            return back()->with('error', '該角色尚有管理員使用中，無法刪除');
        }

        if (!$admin->isDeveloper() && $role->isSuperRole()) {
            return back()->with('error', '您沒有權限刪除最高管理者角色');
        }

        $role->delete();
        return back()->with('success', '角色已刪除');
    }

    /**
     * 安全檢查：自動補齊權限依賴 (核心防範)
     * 即使前端 JS 失效，後端在存檔前也會根據 config 強制檢查一次
     */
    private function securePermissions($submittedPerms)
    {
        $rawConfig = config('backend_permissions');
        $finalPerms = $submittedPerms;

        foreach ($rawConfig as $group) {
            foreach ($group['subs'] as $subKey => $sub) {
                if (!isset($sub['dependencies'])) continue;

                foreach ($sub['dependencies'] as $actKey => $deps) {
                    $currentKey = "{$subKey}.{$actKey}";

                    // 如果使用者勾選了進階動作（如：編輯），我們自動幫他補上基礎動作（如：瀏覽）
                    if (in_array($currentKey, $finalPerms)) {
                        foreach ($deps as $d) {
                            $finalPerms[] = "{$subKey}.{$d}";
                        }
                    }
                }
            }
        }
        return array_unique($finalPerms); // 移除重複的 key 後回傳
    }
}
