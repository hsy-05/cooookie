<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function index(Request $request)
    {
        // 1. 列表頁新增：統計管理員數量 (users_count)
        $roles = AdminRole::withCount('users')
            ->orderBy('is_system', 'desc')
            ->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', [
            'role' => new AdminRole(),
            'isEdit' => false,
            'pageTitle' => '新增角色',
            'permissionConfig' => config('backend_permissions'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:admin_roles,name']);

        // 儲存權限陣列
        $data = $request->all();
        $data['permissions'] = $request->input('permissions', []);

        AdminRole::create($data);
        return redirect()->route('admin.roles.index')->with('success', '角色新增成功');
    }

    public function edit($id)
    {
        $role = AdminRole::findOrFail($id);
        return view('admin.roles.form', [
            'role' => $role,
            'isEdit' => true,
            'pageTitle' => '編輯角色',
            'permissionConfig' => config('backend_permissions'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = AdminRole::findOrFail($id);
        if ($role->is_system) {
             // 系統角色只能改描述
             $role->update(['description' => $request->description]);
             return redirect()->route('admin.roles.index')->with('success', '系統角色僅更新描述');
        }

        $request->validate(['name' => 'required|unique:admin_roles,name,' . $id]);

        $data = $request->all();
        $data['permissions'] = $request->input('permissions', []);

        $role->update($data);
        return redirect()->route('admin.roles.index')->with('success', '角色更新成功');
    }

    public function destroy($id)
    {
        $role = AdminRole::findOrFail($id);

        // 防呆：無法刪除系統預設
        if ($role->is_system) return back()->with('error', '系統角色無法刪除');

        // 防呆：無法刪除尚有使用者的角色 (透過 users_count 或 關聯查詢)
        if ($role->users()->count() > 0) return back()->with('error', '該角色尚有管理員使用中，請先移除管理員');

        $role->delete();
        return back()->with('success', '角色已刪除');
    }
}
