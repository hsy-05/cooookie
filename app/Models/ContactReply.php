<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ContentHelper;

class ContactReply extends Model
{
    protected $table = 'contact_reply';
    protected $primaryKey = 'reply_id';

    protected $fillable = [
        'contact_id',
        'subject',
        'content',
        'src_ip',
        'admin_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | 存取器 (Accessors)
    |--------------------------------------------------------------------------
    */

    /**
     * 取得回覆內容時自動還原網址
     * 用途：將庫存的 [[SITE_URL]] 轉為當前網域網址
     * @param string $value 原始 HTML 內容
     * @return string
     */
    public function getContentAttribute($value): string
    {
        return ContentHelper::decodeSiteUrl($value ?? '');
    }

    /*
    |--------------------------------------------------------------------------
    | 關聯設定 (Relationships)
    |--------------------------------------------------------------------------
    */

    /**
     * 反向關聯：這筆回覆屬於哪一張聯絡單
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'contact_id');
    }

    /**
     * 關聯管理員：記錄是誰回覆的
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        // 假設你的管理員模型是 User 或 Admin
        return $this->belongsTo(User::class, 'admin_id', 'id')->withDefault([
            'name' => '已刪除管理員'
        ]);
    }
}
