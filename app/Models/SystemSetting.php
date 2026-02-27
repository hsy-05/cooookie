<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{protected $table = 'system_settings';

    // 必須包含這些欄位，createMany 才會成功
    protected $fillable = [
        'parent_id',
        'setting_key',
        'title',
        'setting_value',
        'type',
        'range',
        'upload_dir',
        'is_visible',
        'display_order'
    ];

    /**
     * 定義父子關聯
     */
    public function children()
    {
        return $this->hasMany(SystemSetting::class, 'parent_id', 'id');
    }

    /**
     * 清除快取邏輯 (維持不變，確保效能)
     */
    protected static function booted()
    {
        static::saved(fn() => Cache::forget('site_settings'));
    }

    /**
     * 全域抓取設定 (只抓有 key 的設定項)
     */
    public static function getAllSettings()
    {
        return Cache::remember('site_settings', 86400, function () {
            return self::whereNotNull('setting_key')
                       ->pluck('setting_value', 'setting_key')
                       ->toArray();
        });
    }
}
