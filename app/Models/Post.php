<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ContentHelper;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        // 其他欄位
    ];

    /**
     * 取得 content 時，自動把 [[SITE_URL]] 換成完整網址
     */
    public function getContentAttribute($value)
    {
        return ContentHelper::decodeSiteUrl($value);
    }

    /**
     * 設定 content 時，自動把完整網址換成 [[SITE_URL]]
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = ContentHelper::encodeSiteUrl($value);
    }
}
