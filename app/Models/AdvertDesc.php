<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertDesc extends Model
{
    protected $table = 'advert_desc';

    // 因為是複合主鍵 (adv_id + lang_id)，需關閉自增
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'adv_id',
        'lang_id',
        'adv_name', // 修正為正確的資料表欄位名
    ];

    /**
     * 回到廣告主表
     */
    public function advert()
    {
        return $this->belongsTo(Advert::class, 'adv_id', 'adv_id');
    }

    public $timestamps = true; // 預設是 true，會自動管理 created_at 和 updated_at
}
