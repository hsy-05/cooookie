<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 日誌紀錄
use App\Traits\HasImageFields; // 圖片處理

class NewsCategory extends Model
{
    use Loggable, HasImageFields;

    /**
     * 【關鍵優化】定義圖片欄位
     * 當此 Model 執行 delete() 時，HasImageFields Trait 會自動讀取此陣列，
     * 並將硬碟中對應的檔案刪除，無需在 Controller 手動處理。
     */
    protected array $imageFields = ['image_url'];

    // 關閉自動監聽，改在 Controller 手動紀錄，確保標題正確
    public $enableAutoLog = false;

    // 定義 Log 顯示的模組名稱
    public $logName = '消息分類';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'news_category';

    // 指定主鍵欄位
    protected $primaryKey = 'cat_id';

    // 主鍵是 int 並且是 auto-increment
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

    /**
     * 可批量填入的欄位
     */
    protected $fillable = [
        'parent_id',
        'parent_ids',
        'super_id',
        'is_visible',
        'display_order',
        'image_url'
    ];

    /**
     * Model 初始化
     */
    protected static function boot()
    {
        parent::boot();

        // 監聽刪除事件
        static::deleting(function ($item) {
            // 自動刪除關聯的語系描述
            // 使用 delete() 而非 truncate() 確保觸發 NewsDesc 可能有的事件
            $item->descs()->delete();
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                  關聯設定                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * 多語系：取得所有語言的描述資料
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function descs()
    {
        // 這裡的 NewsCategoryDesc::class 在複製時改為對應的 Desc Model
        return $this->hasMany(NewsCategoryDesc::class, 'cat_id', 'cat_id');
    }

    /**
     * 多語系：取得目前選定語系的描述資料
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentDesc()
    {
        // 預設語系防呆：若 Session 無值則採預設值 1
        $langId = session('lang_id') ?? 1;

        return $this->hasOne(NewsCategoryDesc::class, 'cat_id', 'cat_id')
                    ->where('lang_id', $langId);
    }

    /**
     * 取得該分類下的所有項目
     * 用 items 取代 news，複製到 Product 時就不用改名
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        // 複製時僅需更換 News::class
        return $this->hasMany(News::class, 'cat_id', 'cat_id');
    }

    /**
     * 樹狀結構：取得子分類
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'cat_id')
                    ->orderBy('display_order', 'asc');
    }

    /**
     * 樹狀結構：取得父分類
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id', 'cat_id');
    }

    /* -------------------------------------------------------------------------- */
    /*                                  資料存取器                                 */
    /* -------------------------------------------------------------------------- */

    /**
     * 取得用於 Log 或顯示的名稱
     * 這裡使用了 Laravel 的 Accessor 寫法
     *
     * @return string
     */
    public function getLogTitleAttribute(): string
    {
        // 防呆：先抓當前語系名稱，抓不到抓第一筆描述，再抓不到就回傳'未命名'
        return $this->currentDesc->name
               ?? $this->descriptions->first()->name
               ?? '未命名' . $this->logName;
    }
}
