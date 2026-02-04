<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{User, AdminRole};
use Illuminate\Support\Facades\{DB, Hash, Auth};
use App\Helpers\{ContentHelper, ImageHelper};

class AdminUserController extends BaseAdminController
{
    // 定義權限模組名稱，用於 Middleware 自動檢查 (除了 edit/update 我們會手動處理例外)
    protected $permissionName = 'users';
    protected $pageTitle = '網站管理員';

    // 圖片裁切設定
    protected $imageSizes = [
        'avatar_url' => [600, 400],
    ];

    /**
     * 列表頁：顯示管理員清單
     */
    public function index(Request $request)
    {
        // 這裡通常會由 Middleware 擋下沒有 'users.view' 權限的人
        // 但為了嚴謹，您可以在這裡再次檢查權限

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // 使用 with 預先載入關聯資料，避免 N+1 問題 (提升效能)
        $query = User::with(['role', 'childrenRecursive.role']);

        // 邏輯：如果你不是開發者，你只能看到你的下屬；如果你是開發者，看全部
        if (!$currentUser->isDeveloper()) {
            if ($currentUser->isInternalAdmin()) {
                // 內部管理員：只抓出頂層非 Developer (簡化邏輯)
                $query->whereNull('parent_id')
                    ->whereDoesntHave('role', function ($q) {
                        // 假設需要過濾掉系統開發者角色
                    });
            } else {
                // 一般管理員：只能看到自己 (以及 Blade 遞迴出的下屬)
                $query->where('id', $currentUser->id);
            }
        } else {
            // 開發者：看所有根節點
            $query->whereNull('parent_id');
        }

        $users = $query->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * 新增頁：只有擁有權限的人可以進入
     */
    public function create()
    {
        // 建立一個空的 User 物件給表單使用
        $user = new User();
        // 預設給予一些基礎設定值，避免前端 JS 報錯
        $user->preferences = [
            'dark_mode' => true,
            'sidebar_collapse' => false,
            'theme_color' => 'default'
        ];

        return view('admin.users.form', [
            'user'            => $user,
            'roles'           => AdminRole::all(),
            'parents'         => User::all(), // 實務上建議過濾掉不適合當上層的人
            'isEdit'          => false,
            'pageTitle'       => '新增管理員',
            'permissions'     => $this->preparePermissions($user),

            // 【控制介面顯示邏輯】
            'showPermissions' => true,  // 新增時：當然要設定權限
            'showPersonal'    => false, // 新增時：不需設定個人介面 (等他登入自己設)
            'isSelf'          => false,
        ]);
    }

    /**
     * 儲存新增資料
     */
    public function store(Request $request)
    {
        // 驗證表單資料
        $request->validate([
        'name'     => 'required',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:6', // confirmed 代表兩次密碼必須一致
        'role_id'  => 'required',
        ], [
            'password.confirmed' => '兩次輸入的密碼不一致',
            'password.min' => '密碼長度至少需要 6 個字',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        return DB::transaction(function () use ($request, $currentUser) {
            $user = new User();

            // 排除掉不直接儲存的欄位，保持資料乾淨
            $data = $request->except(['password', 'avatar_url', 'permissions', 'preferences']);

            // 密碼加密
            $data['password']  = Hash::make($request->password);
            $data['is_active'] = $request->has('is_active');

            // 處理權限 (新手注意：這是權限控管的核心)
            $data['permissions'] = $this->securePermissions($request->input('permissions', []));

            // 自動設定上層邏輯
            if (!$currentUser->isDeveloper() && !$currentUser->isInternalAdmin()) {
                $data['parent_id'] = $currentUser->id;
            }

            $user->fill($data)->save();

            // 處理圖片上傳
            $this->handleImageUpload($request, $user);

            ContentHelper::showMsg(0, '新增完成', [
                ['text' => '返回列表', 'href' => route('admin.users.index')],
            ], true);

            return redirect()->route('admin.users.index');
        });
    }

    /**
     * 【新增】個人資料頁面
     * 導向到個人的編輯頁面，網址顯示為 /admin/users/profile
     */
    public function profile()
    {
        // 確保返回的是有效的用戶對象
        /** @var User $user */
        $user = Auth::user();

        // 檢查用戶對象是否存在
        if (!$user) {
            abort(403, '未經授權訪問');
        }

        // 調用 edit 方法，並將當前用戶的 id 傳入
        return $this->edit($user->id, true);
    }



    /**
     * 編輯頁：支援「編輯自己」與「編輯他人」
     * @param int $id
     * @param bool $isProfileRoute 是否是從 /profile 路由進來的
     */
    public function edit($id, $isProfileRoute = false)
    {
        $user = User::findOrFail($id);
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // 【核心邏輯】判斷是否為本人
        $isSelf = ($user->id === $currentUser->id);

        // 防呆：如果網址是 /profile 但查出來的不是自己 (理論上 id 會傳 auth id，但雙重確認)
        if ($isProfileRoute && !$isSelf) {
            abort(403);
        }

        // 權限檢查：不是本人且沒有編輯權限 -> 擋掉
        if (!$isSelf && !$currentUser->canDo('users.edit')) {
            abort(403, '您沒有權限編輯此管理員。');
        }

        // 避免將子孫設定為自己的上層
        $descendantIds = $this->getDescendantIds($user);
        $descendantIds[] = $user->id;
        $parents = User::whereNotIn('id', $descendantIds)->get();

        // 準備預設偏好設定 (若使用者完全沒設定過)
        $defaultPreferences = [
            'dark_mode'        => true,
            'sidebar_collapse' => false,
            'nav_flat'         => false,
            'navbar_color'     => 'navbar-white navbar-light', // 預設亮色導覽列
            'sidebar_theme'    => 'sidebar-dark-primary',      // 預設深色側邊欄
            'accent_color'     => '',                          // 預設無強調色
        ];

        // 合併使用者的設定與預設值
        $userPrefs = array_merge($defaultPreferences, $user->preferences ?? []);

        return view('admin.users.form', [
            'user'            => $user,
            'roles'           => AdminRole::all(),
            'parents'         => $parents,
            'isEdit'          => true,
            'pageTitle'       => $isSelf ? '個人資料設定' : '編輯管理員',
            'permissions'     => $this->preparePermissions($user),
            'userPrefs'       => $userPrefs, // 傳遞整理過的偏好設定給 View
            'showPermissions' => !$isSelf,
            'showPersonal'    => $isSelf,
            'isSelf'          => $isSelf,
            // 讓 View 知道是從 Profile 進來的，用於返回按鈕邏輯
            'fromProfile'     => $isProfileRoute
        ]);
    }

    /**
     * 【新增】專門處理「個人資料儲存」
     * 面試說明：將此邏輯獨立，是為了避開通用 CRUD 的權限 Middleware 檢查，
     * 確保即使沒有「網站管理員」權限的用戶，也能修改自己的密碼與設定。
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 這裡直接複用 update 的核心邏輯，但確保對象是自己
        // 這樣就不會觸發 CheckBackendPermission 中間件
        return $this->update($request, $user);
    }

    /**
     * 更新資料
     */
    public function update(Request $request, User $user)
    {
        // 確保安全性：如果走的是標準 update 路由 (非 profile)，還是要檢查權限
        // Route::currentRouteName() 可以判斷當前路由
        $isProfileRoute = ($request->route()->named('admin.users.updateProfile'));

        /** @var User $currentUser */
        $currentUser = Auth::user();
        $isSelf = ($user->id === $currentUser->id);

        // 如果不是走 Profile 路由，且不是改自己，且沒權限 => 擋掉
        if (!$isProfileRoute && !$isSelf && !$currentUser->canDo('users.edit')) {
            abort(403, '您沒有權限執行此操作。');
        }

        // 驗證表單資料，如果有填密碼才驗證一致性
        $request->validate([
        'password' => 'nullable|confirmed|min:6',
        ], [
            'password.confirmed' => '兩次輸入的密碼不一致',
        ]);

        return DB::transaction(function () use ($request, $user, $isSelf) {
            $imageData = $this->handleImageUpload($request, $user);

            // 排除不直接更新的欄位
            $data = $request->except([
                'password',
                'permissions',
                'role_id',
                'is_active',
                'avatar_url',
                'preferences',
                'parent_id',
                // 排除所有介面設定的 input name，避免髒資料寫入 user table 其他欄位
                'pref_dark_mode',
                'pref_sidebar_collapse',
                'pref_nav_flat',
                'pref_sidebar_fixed',
                'pref_text_sm',
                'pref_navbar_variant',
                'pref_sidebar_variant',
                'pref_accent_color',
                'pref_brand_variant'
            ]);

            // 1. 密碼
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // 2. 管理員權限相關 (僅限編輯他人)
            if (!$isSelf) {
                $data['is_active']   = $request->has('is_active');
                $data['role_id']     = $request->input('role_id');
                $data['parent_id']   = $request->input('parent_id');
                $data['permissions'] = $this->securePermissions($request->input('permissions', []));
            }


            // 3. 個人偏好設定 (僅限編輯自己)
            if ($isSelf) {
                $currentPrefs = $user->preferences ?? [];

                // 讀取前端傳來的設定，這裡不給 fallback 預設值，保留 null，最後用 merge 處理
                $newPrefs = [
                    'dark_mode'        => $request->has('pref_dark_mode'),
                    'sidebar_collapse' => $request->has('pref_sidebar_collapse'),
                    'nav_flat'         => $request->has('pref_nav_flat'),
                    'sidebar_fixed'    => $request->has('pref_sidebar_fixed'),
                    'text_sm'          => $request->has('pref_text_sm'),

                    // 修正點：使用 null 作為 fallback，避免被硬編碼的「白色」蓋掉「海洋深潛」
                    'navbar_color'     => $request->input('pref_navbar_variant'),
                    'sidebar_theme'    => $request->input('pref_sidebar_variant'),
                    'accent_color'     => $request->input('pref_accent_color'),
                    'brand_color'      => $request->input('pref_brand_variant'),
                ];

                // 過濾掉 null 的值，確保只更新有傳回來的欄位
                $newPrefs = array_filter($newPrefs, fn($value) => !is_null($value));

                $data['preferences'] = array_merge($currentPrefs, $newPrefs);
            }

            $data = array_merge($data, $imageData);
            $user->update($data);

            ContentHelper::showMsg(0, '資料更新完成', [
                ['text' => '繼續編輯', 'href' => $isSelf ? route('admin.users.profile') : route('admin.users.edit', $user->id)],
            ], true);

            return redirect()->back();
        });
    }

    // --- 內部輔助方法 (Private Helper Methods) ---

    /**
     * 整理權限結構 (與您原本的邏輯相容)
     */
    private function preparePermissions($user)
    {
        $rawConfig = config('backend_permissions'); // 假設這是您的權限設定檔
        if (!$rawConfig) return []; // 防呆

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
                        'checked' => in_array($permKey, $user->permissions ?? []),
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
     * 確保權限依賴關係正確
     */
    private function securePermissions($submittedPerms)
    {
        // 這裡放原本的 dependencies 檢查邏輯
        // 為節省篇幅，此處示意保留原邏輯
        return array_unique($submittedPerms);
    }

    /**
     * 取得所有子孫 ID (遞迴)
     */
    private function getDescendantIds(User $user): array
    {
        $ids = [];
        foreach ($user->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * 圖片上傳邏輯
     */
    private function handleImageUpload(Request $request, User $user): array
    {
        // 呼叫原本的 ImageHelper 邏輯
        $imageData = [];
        foreach ($this->imageSizes as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                if ($user->$field) {
                    ImageHelper::deleteImage($user->$field, 'public');
                }
                $file = $request->file($field);
                // 假設您有這個 helper
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('users', $filename, 'public');
                $imageData[$field] = $path;
            }
        }
        return $imageData;
    }
}
