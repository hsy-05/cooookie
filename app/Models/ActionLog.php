<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    // 設定可以被寫入的欄位
    protected $fillable = ['user_id', 'action', 'log_info', 'ip_address'];

    // 關聯 User，讓我們知道 user_id 是誰
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => '未知/系統' // 防呆：如果 user_id 是 null，顯示這個名字
        ]);
    }

    // 搜尋過濾器 (Scope)，讓 Controller 程式碼保持乾淨
    public function scopeFilter($query, $filters)
    {
        // 搜尋關鍵字
        if (isset($filters['search']) && $filters['search']) {
            $query->where(function($q) use ($filters) {
                $q->where('log_info', 'like', '%'.$filters['search'].'%')
                  ->orWhere('ip_address', 'like', '%'.$filters['search'].'%')
                  ->orWhereHas('user', function($u) use ($filters){
                      $u->where('name', 'like', '%'.$filters['search'].'%');
                  });
            });
        }

        // 搜尋日期區間
        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }
}
