<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ActionLog
 * * @package App\Models
 * @description 系統操作紀錄模型，負責紀錄並格式化管理員的操作行為。
 */
class ActionLog extends Model
{
    /**
     * 可批量寫入的欄位
     * @var array
     */
    protected $fillable = [
        'admin_id',
        'action',
        'log_info',
        'ip_address'
    ];

    /*
    |--------------------------------------------------------------------------
    | 關聯設定 (Relationships)
    |--------------------------------------------------------------------------
    */

    /**
     * 關聯至管理員帳號
     * * @return BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => '系統自動執行'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 存取器 (Accessors) - 格式化顯示邏輯
    |--------------------------------------------------------------------------
    */

    /**
     * 操作紀錄內容
     * 規則：若 log_info 開頭包含 action 名稱，則將其移除以避免重複顯示。
     * 例如：「編輯」-「編輯消息: XX」 -> 「編輯」-「消息: XX」
     * * @return string
     */
    public function getActionLogInfoAttribute(): string
    {
        if (!$this->action) return $this->log_info;

        // 使用 preg_replace 僅匹配字串開頭的動作名稱，u 修正位元組問題
        return preg_replace('/^' . preg_quote($this->action, '/') . '\s*/u', '', $this->log_info);
    }

    /**
     * 取得動作對應的 Bootstrap 顏色樣式
     * * @return string
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            '新增' => 'success',
            '刪除' => 'danger',
            '編輯' => 'info',
            '登入' => 'primary',
            '登出' => 'secondary',
            default => 'dark',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | 查詢作用域 (Scopes)
    |--------------------------------------------------------------------------
    */

    /**
     * 操作紀錄過濾器
     * * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters 篩選參數
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters)
    {
        // 關鍵字搜尋 (支援 內容、IP、操作者姓名)
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $search = $filters['search'];
                $q->where('log_info', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('admin', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 日期區間篩選
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }
}
