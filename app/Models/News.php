<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入日誌 Trait
use App\Traits\HasImageFields; // 引入圖片處理 Trait

class News extends Model
{
    use Loggable, HasImageFields; // 同時使用多個 Trait

    /**
     * 【關鍵優化】定義圖片欄位
     * 當此 Model 執行 delete() 時，HasImageFields Trait 會自動讀取此陣列，
     * 並將硬碟中對應的檔案刪除，無需在 Controller 手動處理。
     */
    protected array $imageFields = ['image_url'];

    // 關閉自動監聽，改在 Controller 手動紀錄，確保標題正確
    public $enableAutoLog = false;

    // 定義 Log 顯示的模組名稱
    public $logName = '消息';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'news';

    // 指定主鍵欄位
    protected $primaryKey = 'news_id';

    // 主鍵是 int 並且是 auto-increment
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

    /**
     * 控制可批量填入的欄位
     */
    protected $fillable = [
        'cat_id',
        'is_visible',
        'is_visible_home',
        'display_order',
        'image_url'
    ];

    /**
     * 關聯：一則資料會有多個語系描述
     */
    public function descs()
    {
        return $this->hasMany(NewsDesc::class, 'news_id', 'news_id');
    }

    /**
     * 關聯：取得目前語系的一筆描述資料
     */
    public function desc()
    {
        // 優先從 Session 抓取，若無則預設為 1
        $langId = session('lang_id') ?? 1;

        return $this->hasOne(NewsDesc::class, 'news_id', 'news_id')
            ->where('lang_id', $langId);
    }

    /**
     * 關聯：每則資料屬於某一個分類
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'cat_id', 'cat_id');
    }

    /**
     * 存取器：自動取得目前語系的標題 (Accessor)
     * 用法：$news->title
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();

        // 靜態快取語系 ID，避免在同一 Request 內重複查詢資料庫
        static $langIdCache = null;
        if ($langIdCache === null) {
            $langIdCache = \App\Models\Language::where('code', $locale)->value('lang_id');
        }

        // 效能優化：如果已經 load 了 descs，就直接從 collection 找，不額外查 DB
        if ($this->relationLoaded('descs')) {
            return optional($this->descs->firstWhere('lang_id', $langIdCache))->title;
        }

        return optional($this->descs()->where('lang_id', $langIdCache)->first())->title;
    }

    /**
     * 操作紀錄中的標題來源
     */
    public function getLogTitleAttribute()
    {
        // 優先抓取目前關聯到的 desc 標題，若無則抓第一個語系
        return $this->desc->title ?? ($this->descs->first()->title ?? '未命名消息');
    }
}
