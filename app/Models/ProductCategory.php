<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入日誌 Trait
use App\Traits\HasImageFields; // 引入圖片處理 Trait

class ProductCategory extends Model
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
    public $logName = '產品分類';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'product_category';

    // 指定主鍵欄位
    protected $primaryKey = 'cat_id';

    // 主鍵是 int 並且是 auto-increment
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

    // 👉 控制可批量填入的欄位（對 create / update 才能用）
    protected $fillable = [
        'parent_id',
        'parent_ids',
        'super_id',
        'is_visible',
        'display_order',
        'image_url'
    ];

    /**
     * 與描述表一對多（多語系）
     */
    public function descs()
    {
        return $this->hasMany(ProductCategoryDesc::class, 'cat_id', 'cat_id');
    }

    /**
     * 取得目前語系的一筆描述資料
     * $langId 可傳入特定 lang_id，若為 null 則從 session 或系統預設取
     * 當你要抓語系內容時可以直接使用： $product->currentDesc->title
     */
    public function currentDesc()
    {
        // 如果 Session 沒設定，預設語系為 1
        $langId = session('lang_id') ?? 1;

        // hasOne 並加上 where 語言過濾
        return $this->hasOne(ProductCategoryDesc::class, 'cat_id', 'cat_id')
            ->where('lang_id', $langId);
    }

    // 在 ProductCategory 模型中添加以下方法
    public function product()
    {
        // 假設 Product 模型中有一個 cat_id 外鍵，指向 ProductCategory
        return $this->hasMany(Product::class, 'cat_id', 'cat_id');
    }

    // 在 Product 模型中添加以下方法
    public function category()
    {
        // 假設 Product 模型中的外鍵是 cat_id
        return $this->belongsTo(ProductCategory::class, 'cat_id', 'cat_id');
    }

    /**
     * 自我關聯：取得子分類 (一對多)
     */
    public function children()
    {
        // 外部鍵是 parent_id，本地鍵是 cat_id
        return $this->hasMany(ProductCategory::class, 'parent_id', 'cat_id')
            ->orderBy('display_order', 'asc');
    }

    /**
     * 自我關聯：取得父分類 (反向一對多)
     */
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id', 'cat_id');
    }

    public function getLogTitleAttribute()
    {
        // 嘗試抓取第一筆關聯的標題，抓不到就回傳 '未命名'
        // 注意：如果你是用語系，可以寫 ->where('lang', 'zh-TW')->first()
        return $this->descs->first()->name ?? '未命名產品分類';
    }
}
