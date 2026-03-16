<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Traits\HasTagFields;
use App\Helpers\TagHelper;

/**
 * 系統參數設定模型
 * 管理全站全域變數，如 SEO 設定、Logo 路徑或社群連結
 *
 * @property string $setting_value 儲存在資料庫的原始值
 * @property string $type 設定類型 (text, tags, image 等)，決定顯示與讀取邏輯
 */
class SystemSetting extends Model
{
    /**
     * 引入標籤欄位自動轉換功能
     * 配合 $tagFields 定義，讓模型在讀取時自動處理標籤格式
     */
    use HasTagFields;

    // 指定對應的資料表
    protected $table = 'system_settings';

    // 定義可批次更新的欄位白名單
    protected $fillable = [
        'parent_id', 'setting_key', 'title', 'description',
        'setting_value', 'type', 'config', 'upload_dir',
        'is_visible', 'display_order'
    ];

    /**
     * 讀取器攔截：處理特殊類型的資料轉換
     * 因為系統設定的欄位較為動態，我們必須根據 type 欄位的定義，
     * 決定 setting_value 讀出來時是否要從「逗號字串」轉回「陣列」。
     *
     * @param string $key 屬性名稱
     * @return mixed
     */
    public function getAttribute($key)
    {
        // 取得父類別處理後的原始值
        $value = parent::getAttribute($key);

        // 如果讀取的欄位是 setting_value，且這筆設定的型態被標記為標籤 (tags)
        // 且內容目前還是字串格式，就透過 Helper 轉成陣列，方便前端直接使用
        if ($key === 'setting_value' && $this->type === 'tags' && is_string($value)) {
            return TagHelper::toArray($value);
        }

        return $value;
    }

    /**
     * 取得設定屬性中的選項清單
     * 用於 type 為 select 或 radio 時，從 config 欄位解出可選的內容
     *
     * @return array
     */
    public function getOptionsAttribute(): array
    {
        // 若 config 內沒有定義 options，預設回傳空陣列避免前端噴錯
        return $this->config['options'] ?? [];
    }

    /**
     * 模型事件：自動化快取管理
     * 為了確保前台看到的永遠是最新的設定，當後台有任何設定被儲存或更新時，
     * 我們就強制清除 site_settings 快取，下次讀取時系統會自動重新抓取。
     */
    protected static function booted()
    {
        static::saved(fn() => Cache::forget('site_settings'));
    }

    /**
     * 定義父子關聯 (頁籤或群組分類)
     * 用於在設定頁面將設定值依照 parent_id 進行分組顯示
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(SystemSetting::class, 'parent_id', 'id')
                    ->orderBy('display_order', 'asc');
    }

    /**
     * 全域抓取所有系統設定 (靜態方法)
     * 採用快取機制，預設快取 24 小時。這會將所有設定轉為 key => value 的格式，
     * 讓全站各處可以快速調用特定 key 的設定值，而不需頻繁操作資料庫。
     *
     * @return array
     */
    public static function getAllSettings()
    {
        return Cache::remember('site_settings', 86400, function () {
            // 排除沒有設定 key 的分組標題，只抓取真正的數值
            return self::whereNotNull('setting_key')
                       ->pluck('setting_value', 'setting_key')
                       ->toArray();
        });
    }
}
