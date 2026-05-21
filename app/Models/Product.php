<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入日誌 Trait
use App\Traits\HasImageFields; // 引入圖片處理 Trait

class Product extends Model
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
    public $logName = '產品';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'product';

    // 指定主鍵欄位
    protected $primaryKey = 'product_id';

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
        'image_url',
        'price'
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
            // 使用 delete() 而非 truncate() 確保觸發 ProductDesc 可能有的事件
            $item->descs()->delete();

            // 註：圖片刪除已由 HasImageFields Trait 處理，此處不需重複寫
        });
    }

    /**
     * 關聯：一則資料會有多個語系描述
     */
    public function descs()
    {
        return $this->hasMany(ProductDesc::class, 'product_id', 'product_id');
    }

    /**
     * 關聯：取得目前語系的一筆描述資料
     */
    public function currentDesc()
    {
        // 優先從 Session 抓取，若無則預設為 1
        $langId = session('lang_id') ?? 1;

        return $this->hasOne(ProductDesc::class, 'product_id', 'product_id')
            ->where('lang_id', $langId);
    }

    /**
     * 關聯：每則資料屬於某一個分類
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'cat_id', 'cat_id');
    }

    /**
     * 存取器：自動取得目前語系的標題 (Accessor)
     * 用法：$product->title
     */
    public function getTitleAttribute()
    {
        // 強制轉型為整數，避免 Session 抓到的是字串 "2" 而資料庫是數字 2
        $langId = (int)(session('lang_id'));

        if ($this->relationLoaded('descs')) {
            // 使用 filter 確保能精確比對
            $desc = $this->descs->first(function ($item) use ($langId) {
                return (int)$item->lang_id === $langId;
            });

            // 如果該語系找不到，回傳第一筆（通常是中文）作為備援
            return $desc ? $desc->title : ($this->descs->first()->title ?? '--');
        }

        // 沒預載時的處理
        $desc = $this->descs()->where('lang_id', $langId)->first();
        return $desc ? $desc->title : ($this->descs()->first()->title ?? '--');
    }

    /**
     * 操作紀錄中的標題來源
     */
    public function getLogTitleAttribute()
    {
        // 優先抓取目前關聯到的 desc 標題，若無則抓第一個語系
        return $this->currentDesc->title ?? ($this->descs->first()->title ?? '未命名產品');
    }

}
