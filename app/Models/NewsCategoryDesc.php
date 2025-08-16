<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategoryDesc extends Model
{
    protected $table = 'news_category_desc';

    // 使用複合主鍵時，Eloquent 的自增設定需關閉
    public $incrementing = false;
    protected $primaryKey = null; // 我們以 (cat_id, lang_id) 為主鍵

    protected $fillable = [
        'cat_id',
        'lang_id',
        'name',
        'description',
        'content',
    ];

    /**
     * 關聯回主表
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'cat_id', 'cat_id');
    }
}
