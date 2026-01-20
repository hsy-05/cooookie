<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // 為了 Q2 的需求，我們將資料分為兩組
        // 這裡需要用 whereHas 來篩選「關聯表(roles)的欄位」

        // 1. 系統核心管理員 (Role is_system = 1) 且是頂層 (parent_id = null)
        $systemRoots = User::whereNull('parent_id')
            ->whereHas('role', function($q) {
                $q->where('is_system', 1);
            })
            ->with(['role', 'childrenRecursive.role']) // 預加載防止 N+1
            ->get();

        // 2. 一般網站管理員 (Role is_system = 0) 且是頂層
        $normalRoots = User::whereNull('parent_id')
            ->whereHas('role', function($q) {
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
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'role_id' => 'required',
        ]);

        $data = $request->except(['password']);
        $data['password'] = Hash::make($request->password);
        $data['is_active'] = $request->has('is_active');

        // 儲存個人權限 (陣列)
        $data['permissions'] = $request->input('permissions', []);

        // 若無選擇父層且非系統管理員，強制父層為自己
        if (!Auth::user()->role->is_system && empty($data['parent_id'])) {
            $data['parent_id'] = Auth::id();
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', '新增成功');
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

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 防呆：防止自己停用自己
        if ($id == Auth::id() && !$request->has('is_active')) {
             return back()->with('error', '您無法停用自己的帳號！');
        }

        $data = $request->except(['password']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $data['is_active'] = $request->has('is_active');
        $data['permissions'] = $request->input('permissions', []);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', '更新成功');
    }

    // 輔助方法：取得所有子孫 ID
    private function getDescendantIds($user) {
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
        if ($user->id === auth()->id) {
            return back()->with('error', '不能刪除自己！');
        }
        $user->delete();
        return back()->with('success', '管理員已刪除');
    }
}

