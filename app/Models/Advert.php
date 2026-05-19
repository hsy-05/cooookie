<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入操作紀錄
use App\Traits\HasImageFields; // 引入圖片處理 Trait

class Advert extends Model
{
    use Loggable, HasImageFields;

    // 定義圖片欄位：當刪除資料時，HasImageFields 會自動清理這些欄位對應的檔案
    protected array $imageFields = ['adv_img_url', 'adv_img_m_url'];

    // 關閉自動監聽 Log：改在 Controller 手動紀錄，才能確保抓到正確語系的標題
    public $enableAutoLog = false;

    // Log 模組名稱
    public $logName = '廣告';

    // 資料表與主鍵設定
    protected $table = 'advert';
    protected $primaryKey = 'adv_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'cat_id',
        'adv_img_url',
        'adv_img_m_url',
        'adv_link_url',
        'display_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * 關聯：一則廣告對應多個語系名稱
     */
    public function descs()
    {
        return $this->hasMany(AdvertDesc::class, 'adv_id', 'adv_id');
    }

    /**
     * 關聯：取得「目前語系」的描述資料 (根據 Session 語系)
     */
    public function currentDesc()
    {
        $langId = session('lang_id') ?? 1;
        return $this->hasOne(AdvertDesc::class, 'adv_id', 'adv_id')
            ->where('lang_id', $langId);
    }

    /**
     * 關聯：所屬分類
     */
    public function category()
    {
        return $this->belongsTo(AdvertCategory::class, 'cat_id', 'cat_id');
    }

    /**
     * 操作紀錄顯示用的標題來源 (Accessor)
     */
    public function getLogTitleAttribute()
    {
        // 優先抓取目前語系名稱，若無則抓該廣告第一個語系名稱
        return $this->currentDesc->adv_name ?? ($this->descs->first()->adv_name ?? '未命名廣告');
    }
}
