<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    // 指定 table 與 primaryKey（cat_id）
    protected $table = 'news_category';
    protected $primaryKey = 'cat_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // 可以大量賦值的欄位
    protected $fillable = [
        'parent_id',
        'parent_ids',
        'super_id',
        'is_visible',
        'display_order',
    ];

    /**
     * 與描述表一對多（多語系）
     */
    public function descs()
    {
        return $this->hasMany(NewsCategoryDesc::class, 'cat_id', 'cat_id');
    }

    /**
     * 取得指定語系的描述（helper）
     * $langId 可傳入特定 lang_id，若為 null 則從 session 或系統預設取
     */
    public function desc($langId = null)
    {
        $langId = $langId ?: session('lang_id') ?: config('app.locale'); // 你可能需要自行 mapping
        return $this->descs()->where('lang_id', $langId)->first();
    }
}

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
