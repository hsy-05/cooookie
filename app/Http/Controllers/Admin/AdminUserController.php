<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Models\{User, AdminRole};
use Illuminate\Support\Facades\{DB, Log, Hash, Auth};
use App\Helpers\{ContentHelper, ImageHelper};

class AdminUserController extends BaseAdminController
{
    // 設定權限名稱，自動綁定 users.view, users.create, users.delete
    protected $permissionName = 'users';

    protected $pageTitle = '網站管理員';

    // 設定圖片配置，方便未來擴充
    protected $imageSizes = [
        'avatar_url' => [600, 400],
        // 'thumbnail' => [300, 200], // 縮圖範例
        // 'banner' => [1200, 500],   // Banner 範例
    ];

    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // 為了 Q2 的需求，我們將資料分為兩組
        // 這裡需要用 whereHas 來篩選「關聯表(roles)的欄位」

        // 1. 系統核心管理員 (Role is_system = 1) 且是頂層 (parent_id = null)
        $systemRoots = User::whereNull('parent_id')
            ->whereHas('role', function ($q) {
                $q->where('is_system', 1);
            })
            ->with(['role', 'childrenRecursive.role']) // 預加載防止 N+1
            ->get();

        // 2. 一般網站管理員 (Role is_system = 0) 且是頂層
        $normalRoots = User::whereNull('parent_id')
            ->whereHas('role', function ($q) {
                $q->where('is_system', 0);
            })
            ->with(['role', 'childrenRecursive.role'])
            ->get();

        // 如果當前使用者不是系統管理員，他只能看到自己 (維持之前的邏輯)
        // 這裡為了展示方便，假設如果是系統管理員可以看到上面兩組
        // 如果是一般管理員，只回傳他自己當作 normalRoots 的一部分
        if (!$currentUser->role->is_system) {
            $systemRoots = collect([]); // 空集合
            $normalRoots = User::where('id', $currentUser->id)->with('childrenRecursive.role')->get();
        }

        return view('admin.users.index', compact('systemRoots', 'normalRoots'));
    }

    public function create()
    {
        // 排除自己所有的子孫，避免選到子孫當父層 (防呆：無窮迴圈)
        // 新增時還沒ID，所以所有現存使用者都能當父層 (除了邏輯上不合理的，但在新增時還好)
        $parents = User::all();

        return view('admin.users.form', [
            'user' => new User(),
            'roles' => AdminRole::all(),
            'parents' => $parents,
            'isEdit' => false,
            'pageTitle' => '新增管理員',
            'permissionConfig' => config('backend_permissions'), // 傳入權限設定
        ]);
    }

    public function store(Request $request)
    {
        // 1. 表單驗證（只負責「資料格式」）
        $request->validate([
            'name'       => 'required',
            'email'      => 'required|email|unique:users,email',
            'avatar_url' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'password'   => 'required|confirmed|min:6',
            'role_id'    => 'required',
        ]);

        return DB::transaction(function () use ($request) {
            try {

                // 2. 建立空的 User（先拿到 Model 實例）
                $user = new User();

                /**
                 * 3. 處理圖片
                 * - 只回傳圖片路徑
                 * - 不直接存 DB
                 */
                $imageData = $this->handleImageUpload($request, $user);

                /**
                 * 4. 文字欄位
                 * - 明確排除檔案欄位，避免 tmp path 被寫進 DB
                 */
                $data = $request->except([
                    'password',
                    'avatar_url',
                ]);

                // 5. 密碼加密
                $data['password'] = Hash::make($request->password);

                // 6. checkbox（未勾選時 request 不會帶值）
                $data['is_active'] = $request->has('is_active');

                // 7. 權限（JSON / array）
                $data['permissions'] = $request->input('permissions', []);

                /**
                 * 8. 父層邏輯防呆
                 * - 非系統管理員
                 * - 又沒選父層
                 * - 強制指定為自己
                 */
                if (!Auth::user()->role->is_system && empty($data['parent_id'])) {
                    $data['parent_id'] = Auth::id();
                }

                /**
                 * 9. 合併圖片欄位
                 * - avatar_url => users/xxx.jpg
                 */
                $data = array_merge($data, $imageData);

                /**
                 * 10. 一次性寫入資料庫
                 * - 新增時用 create / fill + save 都可以
                 */
                $user->fill($data)->save();

                // 11. 成功訊息
                ContentHelper::showMsg(
                    0,
                    '新增完成',
                    [
                        ['text' => '繼續新增', 'href' => route('admin.users.create')],
                        ['text' => '繼續編輯', 'href' => route('admin.users.edit', $user->id)],
                        ['text' => '返回列表', 'href' => route('admin.users.index')],
                    ],
                    true
                );

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error('Users Store Error: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗');
            }
        });
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        // 防呆：編輯時，父層選單不能包含「自己」和「自己的子孫」
        // 這裡用一個簡單的遞迴 ID 收集器來排除
        $descendantIds = $this->getDescendantIds($user);
        $descendantIds[] = $user->id; // 加上自己

        $parents = User::whereNotIn('id', $descendantIds)->get();

        return view('admin.users.form', [
            'user' => $user,
            'roles' => AdminRole::all(),
            'parents' => $parents,
            'isEdit' => true,
            'pageTitle' => '編輯管理員',
            'permissionConfig' => config('backend_permissions'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {

            // 1. 先處理圖片
            $imageData = $this->handleImageUpload($request, $user);

            // 2. 文字欄位（排除檔案欄位）
            $data = $request->except([
                'password',
                ...array_keys($this->imageSizes),
            ]);

            // 3. 密碼
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // 4. checkbox / array
            $data['is_active']   = $request->has('is_active');
            $data['permissions'] = $request->input('permissions', []);

            // 5. 合併圖片路徑
            $data = array_merge($data, $imageData);

            // 6. 一次存進 DB
            $user->update($data);

            ContentHelper::showMsg(
                0,
                '編輯操作完成',
                [
                    ['text' => '繼續編輯', 'href' => route('admin.users.edit', $user->id)],
                    ['text' => '返回列表', 'href' => route('admin.users.index')],
                ],
                true
            );

            return redirect()->back();
        });
    }


    // 輔助方法：取得所有子孫 ID
    private function getDescendantIds($user)
    {
        $ids = [];
        foreach ($user->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('form_error_swal', '不能刪除自己！');
        }
        $user->delete();
        return back()->with('form_success_swal', '刪除成功');
    }


    /**
     * 處理圖片上傳，回傳處理後的圖片路徑並刪除舊圖
     */
    private function handleImageUpload(Request $request, User $user): array
    {
        $imageData = [];

        foreach ($this->imageSizes as $field => [$width, $height]) {
            if ($request->hasFile($field)) {

                // 刪除舊圖
                if ($user->$field) {
                    ImageHelper::deleteImage($user->$field, 'public');
                }

                $file = $request->file($field);
                $processed = ImageHelper::processImage($file, $width, $height, 'center_crop');

                $filename = ImageHelper::generateUniqueFilename($file);
                $path = "users/{$filename}";

                ImageHelper::saveProcessedImage($processed, $path, 'public', 90, 'jpeg');

                // 收集要更新的欄位
                $imageData[$field] = $path;
            }
        }

        return $imageData;
    }
}
