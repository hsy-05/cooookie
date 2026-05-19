<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryDesc extends Model
{
    // 👉 指定資料表
    protected $table = 'product_category_desc';

    /**
     * ❗由於 product_category_desc 沒有 id，也沒有 auto-increment，
     *   只有 (cat_id, lang_id) 當複合主鍵，
     *   Laravel 不能正式支援複合主鍵，
     *   所以要手動關閉 incrementing。
     */
    public $incrementing = false;

    // 👉 主鍵型態是 int（雖然是兩個 key，但 type 還是 int）
    protected $keyType = 'int';

    // 👉 可批量填入的欄位
    protected $fillable = [
        'cat_id',
        'lang_id',
        'name',
        'description',
        'content'
    ];

    /**
     * 關聯回主表
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'cat_id', 'cat_id');
    }
}
