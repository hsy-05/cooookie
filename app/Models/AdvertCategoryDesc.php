<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertCategoryDesc extends Model
{
    // 指定資料表名稱
    protected $table = 'advert_category_desc';

    // 關閉自增主鍵（因為通常是複數主鍵，或不靠單一 ID 維護）
    public $incrementing = false;

    protected $fillable = [
        'cat_id',
        'lang_id',
        'cat_name',
    ];

    /**
     * 關聯回主表
     */
    public function category()
    {
        return $this->belongsTo(AdvertCategory::class, 'cat_id', 'cat_id');
    }
}
