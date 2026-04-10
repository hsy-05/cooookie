<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\{ContentHelper, SummernoteImageHelper};

class Contact extends Model
{
    protected $table = 'contact';
    protected $primaryKey = 'contact_id';

    protected $fillable = [
        'contact_sn', 'fullname', 'email', 'gender',
        'phone', 'subject', 'content', 'ip_address',
        'status'
    ];

    /**
     * Model 初始化行為
     * 用途：定義 Model 生命週期中的自動化行為
     */
    protected static function boot()
    {
        parent::boot();

        // 監聽「刪除中」事件：當此聯絡單執行 delete() 時會先跑這裡
        static::deleting(function ($contact) {
            // 找出所有關聯的回覆紀錄
            $contact->replies()->get()->each(function ($reply) {
                // 先呼叫 Helper 清理回覆內容中的 Summernote 圖片檔案
                SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($reply->content), null);

                // 正式刪除該筆回覆紀錄
                $reply->delete();
            });
        });
    }

    /**
     * 取得內容時的處理 (自動解碼 URL)
     * @param string $value 原始內容
     * @return string
     */
    public function getContentAttribute($value)
    {
        return ContentHelper::decodeSiteUrl($value ?? '');
    }

    /**
     * 關聯回覆紀錄 (一對多)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(ContactReply::class, 'contact_id', 'contact_id');
    }
}
