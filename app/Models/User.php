<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * 特殊權限常數（避免 magic string）
     */
    const PERM_SYSTEM = 'system'; // Developer
    const PERM_ALL    = 'all';    // 內部最高管理員 / 客戶最高管理員

    protected $fillable = [
        'role_id',
        'parent_id',
        'name',
        'email',
        'password',
        'avatar_url',
        'permissions',
        'is_active',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'permissions' => 'array',
        'preferences' => 'array',
        'is_active' => 'boolean',
    ];

    /* =========================
     | 關聯
     ========================= */

    public function role()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /* =========================
     | 身份判斷（面試重點）
     ========================= */

    /**
     * 是否為開發者（Developer）
     * 條件：users.permissions 包含 system
     */
    public function isDeveloper(): bool
    {
        return in_array(self::PERM_SYSTEM, $this->permissions ?? []);
    }

    /**
     * 是否為內部最高管理員
     * 條件：有 all，但不是 developer
     */
    public function isInternalAdmin(): bool
    {
        return !$this->isDeveloper()
            && in_array(self::PERM_ALL, $this->permissions ?? []);
    }

    /**
     * 是否為客戶最高管理員
     * 條件：角色 permissions 包含 all
     */
    public function isCustomerAdmin(): bool
    {
        return $this->role
            && in_array(self::PERM_ALL, $this->role->permissions ?? []);
    }

    /* =========================
     | 統一權限入口（非常重要）
     ========================= */

    /**
     * 統一權限判斷方法
     * Controller / Blade 一律呼叫這個
     */
    public function canDo(string $permission): bool
    {
        // 1. Developer：全部可做
        if ($this->isDeveloper()) {
            return true;
        }

        // 2. 內部最高管理員：不可做 system.*，其餘可
        if ($this->isInternalAdmin() && !str_starts_with($permission, 'system.')) {
            return true;
        }

        // 3. 客戶最高管理員：不可做 system.* / internal.*
        if (
            $this->isCustomerAdmin()
            && !str_starts_with($permission, 'system.')
            && !str_starts_with($permission, 'internal.')
        ) {
            return true;
        }

        // 4. 角色權限
        if ($this->role && in_array($permission, $this->role->permissions ?? [])) {
            return true;
        }

        // 5. 個人額外權限
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * 取得個人化設定 (Helper)
     * @param string $key 設定鍵名
     * @param mixed $default 預設值
     */
    public function getPreference($key, $default = null)
    {
        return data_get($this->preferences, $key, $default);
    }


    /**
     * 定義 AdminLTE 的個人資料頁面 URL
     */
    public function adminlte_profile_url()
    {
        return route('admin.users.profile'); // 指向已定義的 users.profile 路由
    }

    /**
     * 返回 AdminLTE 用戶菜單顯示的頭像 URL
     *
     * @return string
     */
    public function adminlte_image()
    {
        // 假設用戶的頭像存儲在 avatar_url 欄位，若沒有，則使用預設圖片
        return $this->avatar_url ? asset('storage/' . $this->avatar_url) : asset('images/admin/default-avatar.png');
    }

    /**
     * 返回 AdminLTE 用戶菜單顯示的描述
     *
     * @return string
     */
    public function adminlte_desc()
    {
        // 假設我們用角色名稱作為描述，或是其他用戶資料字段
        return $this->role->name ?? '未設定角色'; // 如果有角色，則顯示角色名稱，否則顯示預設文字
    }
}
