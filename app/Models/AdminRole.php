<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入操作紀錄
use App\Traits\HasImageFields; // 引入圖片處理（維持架構統一）

class AdminRole extends Model
{
    use Loggable, HasImageFields;

    protected $table = 'admin_roles';

    // 定義 Log 顯示的模組名稱
    public $logName = '管理員角色';

    protected $fillable = [
        'name',
        'description',
        'permissions'
    ];

    /**
     * 自動轉型
     * 權限在資料庫是 JSON 字串，取出時自動變成 PHP 陣列方便處理
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * 關聯：取得擁有此角色的所有管理員
     */
    public function admins()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * 存取器：供 Log 使用的標題
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    /**
     * 防呆判斷：檢查是否為「超級管理員」
     * 如果權限陣列包含 'all'，代表具備全系統通行權
     * @return bool
     */
    public function isSuperRole(): bool
    {
        return in_array('all', $this->permissions ?? []);
    }
}
