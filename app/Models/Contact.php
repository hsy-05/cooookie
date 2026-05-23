<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\{ContentHelper, SummernoteImageHelper};
use App\Traits\Loggable; // 引入日誌軌跡 Trait

class Contact extends Model
{
    use Loggable; // 啟用操作紀錄功能，解決 writeLog 找不到方法的問題

    /**
     * 定義圖片欄位
     * 當此 Model 執行 delete() 時，HasImageFields Trait 會自動讀取此陣列並清理硬碟檔案
     */
    protected array $imageFields = ['image_url'];

    // 關閉自動監聽，改在 Controller 手動紀錄，確保日誌內容客製化
    public $enableAutoLog = false;

    // 定義 Log 操作紀錄中所顯示的模組名稱
    public $logName = '聯絡我們';

    // 指定操作的資料表名稱
    protected $table = 'contact';

    // 指定主鍵欄位
    protected $primaryKey = 'contact_id';

    // 主鍵欄位是否自動遞增
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

    // 允許批量賦值的欄位安全宣告
    protected $fillable = [
        'contact_sn', 'fullname', 'email', 'gender',
        'phone', 'subject', 'content', 'ip_address',
        'status'
    ];

    /**
     * Model 初始化行為
     * 用途：定義 Model 生命週期中的自動化事件監聽
     */
    protected static function boot()
    {
        parent::boot();

        // 監聽刪除事件：在聯絡單紀錄被刪除前，同步清理其底下的所有回覆紀錄與圖片
        static::deleting(function ($contact) {
            $contact->replies()->get()->each(function ($reply) {
                // 清理回覆內文中由 Summernote 上傳的實體圖片檔案
                SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($reply->content), null);

                // 刪除該筆回覆資料
                $reply->delete();
            });
        });
    }

    /**
     * 取得內容時的修改器
     * 用途：將資料庫內存儲的相對路徑或動態標籤網址，還原為前台可顯示的完整網址
     * @param string|null $value 原始 HTML 內容
     * @return string
     */
    public function getContentAttribute($value)
    {
        return ContentHelper::decodeSiteUrl($value ?? '');
    }

    /**
     * 關聯回覆紀錄（一對多關聯）
     * 用途：取得該諮詢單底下的所有管理員回覆歷程
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(ContactReply::class, 'contact_id', 'contact_id');
    }
}
