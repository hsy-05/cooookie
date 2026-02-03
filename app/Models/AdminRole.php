<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table = 'admin_roles';

    // 移除 is_system，保持乾淨
    protected $fillable = ['name', 'description', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * 關聯：一個角色擁有多個管理員
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * 防呆判斷：是否為超級管理員角色 (擁有 all)
     * 用途：在刪除或編輯時進行保護邏輯
     */
    public function isSuperRole(): bool
    {
        return in_array('all', $this->permissions ?? []);
    }
}
