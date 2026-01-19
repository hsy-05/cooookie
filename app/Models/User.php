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
        'name',
        'email',
        'password',
        'avatar',  // 新增
        'is_active', // 新增
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
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
        // 1. 如果沒有角色，沒權限
        if (!$this->role) return false;

        // 2. 如果是超級管理員 (is_system)，擁有所有權限
        if ($this->role->is_system) return true;

        // 3. 檢查權限陣列中是否有該 Key
        return in_array($permissionKey, $this->role->permissions ?? []);
    }
}
