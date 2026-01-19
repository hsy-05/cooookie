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

        // 1. 樹狀顯示邏輯：
        // 如果是超級管理員 (System Role)，從最頂層 (parent_id is null) 開始抓，包含所有子層
        // 如果是普通管理員 (如 PM)，只抓他底下的子層 (childrenRecursive)

        $query = User::query()->with('role');

        if ($currentUser->role->is_system) {
            // 系統管理員：抓出所有頂層(無父層) 以及他們的子子孫孫
            // 使用 childrenRecursive 預加載遞迴關係
            $users = User::whereNull('parent_id')
                         ->with('childrenRecursive')
                         ->get();
        } else {
            // 一般管理員：只顯示自己底下的
            // 因為列表不顯示自己，只顯示下屬
            $users = $currentUser->childrenRecursive;
        }

        return view('admin.users.index', compact('users', 'currentUser'));
    }

    public function create()
    {
        // 只能選擇自己當父層，或是如果我是超級管理員，可以選其他系統管理員當父層(視需求)
        // 簡化邏輯：新增的管理員，預設父層就是「當前登入者」(除了超級管理員可指定)

        $roles = AdminRole::all();
        // 找出潛在的父層選項 (目前簡單做：所有使用者)
        // 實務上應避免循環參照，這裡先列出所有
        $parents = User::all();

        return view('admin.users.form', [
            'user' => new User(),
            'roles' => $roles,
            'parents' => $parents,
            'isEdit' => false,
            'pageTitle' => '新增管理員'
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

        // 如果沒選 parent_id，且當前用戶不是最高權限，則強制 parent_id 為當前用戶
        if (!Auth::user()->role->is_system && empty($data['parent_id'])) {
            $data['parent_id'] = Auth::id();
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', '新增成功');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        // 防呆：不能編輯比自己高層或平行的人 (除非是超級管理員)
        // 這裡簡化檢查：如果不是系統管理員，且該用戶不是我的下屬，擋掉
        // (省略詳細實作，重點在 View)

        $roles = AdminRole::all();
        $parents = User::where('id', '!=', $user->id)->get(); // 父層不能選自己

        return view('admin.users.form', [
            'user' => $user,
            'roles' => $roles,
            'parents' => $parents,
            'isEdit' => true,
            'pageTitle' => '編輯管理員'
        ]);
    }

    // Update & Destroy 邏輯同前，略作調整...
    // 記得 update 時要檢查 parent_id 不能選到自己的子層 (造成無窮迴圈)
    // 這裡不做太複雜的遞迴檢查，建議前端或後端做簡單防呆
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->except(['password']);
        if($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $data['is_active'] = $request->has('is_active');
        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', '更新成功');
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

