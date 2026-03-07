<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property-read array $options 取得設定選項
 * @property-read array $tags_array 取得標籤陣列
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $casts = [
        'config' => 'array', // 將資料庫 JSON 欄位自動轉換為 PHP 陣列
    ];

    protected $fillable = [
        'parent_id', 'setting_key', 'title', 'description',
        'setting_value', 'type', 'config', 'upload_dir',
        'is_visible', 'display_order'
    ];

    /**
     * 取得單選或下拉選單的選項
     * 用於 radio, select 等類型，讓 View 直接循環
     * @return array
     */
    public function getOptionsAttribute(): array
    {
        return $this->config['options'] ?? [];
    }

    /**
     * 專為 Tag Input 設計的存取器
     * 將資料庫儲存的 "tag1,tag2" 轉為 ['tag1', 'tag2'] 供 Select2 使用
     * @return array
     */
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->setting_value)) return [];

        // 如果原本就是陣列就直接回傳，如果是字串則切分
        return is_array($this->setting_value)
            ? $this->setting_value
            : explode(',', $this->setting_value);
    }

    /**
     * 定義父子關聯 (頁籤分組)
     */
    public function children()
    {
        return $this->hasMany(SystemSetting::class, 'parent_id', 'id')
                    ->orderBy('display_order', 'asc');
    }

    /**
     * 模型事件：儲存後自動清除全域設定快取，確保前台即時更新
     */
    protected static function booted()
    {
        static::saved(fn() => Cache::forget('site_settings'));
    }

    /**
     * 全域抓取設定
     * @return array
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
