<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table = 'admin_roles';
    protected $fillable = ['name', 'description', 'permissions', 'is_system'];

    // 自動將 JSON 轉為陣列
    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
