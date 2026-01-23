<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id', // 新增
        'parent_id', // 新增
        'name',
        'email',
        'password',
        'avatar_url',  // 新增
        'permissions',  // 新增
        'is_active', // 新增
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'parent_id' => 'int',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    // 關聯角色
    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id');
    }

    // 關聯：父層管理員
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // 關聯：子管理員 (直屬)
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // 關聯：子管理員 (遞迴取得所有後代) - 顯示樹狀圖用
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * 檢查是否有權限 (給前端或 Middleware 呼叫)
     * 用法: auth()->user()->hasPermission('news.create')
     */
    public function hasPermission($permissionKey)
    {
        // 1. 如果沒有角色，直接 false
        if (!$this->role) return false;

        // 2. 如果角色的 is_system 是 1 (超級管理員)，直接 true
        if ($this->role->is_system) return true;

        // 3. 取得「角色」擁有的權限
        $rolePermissions = $this->role->permissions ?? [];

        // 4. 取得「個人」額外擁有的權限
        $userPermissions = $this->permissions ?? [];

        // 5. 兩者取聯集 (只要其中一邊有，就有權限)
        // 檢查 Key 是否存在於任一陣列中
        return in_array($permissionKey, $rolePermissions) || in_array($permissionKey, $userPermissions);
    }
}
