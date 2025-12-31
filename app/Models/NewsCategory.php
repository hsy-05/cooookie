<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    // 👉 指定操作的資料表名稱
    protected $table = 'news_category';

    // 👉 指定主鍵欄位（因為不是預設的 id）
    protected $primaryKey = 'cat_id';

    // 👉 主鍵是 int 並且是 auto-increment（因為 migration 用 increments）
    public $incrementing = true;

    // 👉 主鍵的資料型態（Laravel 預設是 string，要改正確）
    protected $keyType = 'int';

    // 👉 控制可批量填入的欄位（對 create / update 才能用）
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
     * 取得目前語系的一筆描述資料
     * $langId 可傳入特定 lang_id，若為 null 則從 session 或系統預設取
     * 當你要抓語系內容時可以直接使用： $news->desc->title
     */
    public function desc()
    {
        // 如果 Session 沒設定，預設語系為 1
        $langId = session('lang_id') ?? 1;

        // hasOne 並加上 where 語言過濾
        return $this->hasOne(NewsCategoryDesc::class, 'cat_id', 'cat_id')
            ->where('lang_id', $langId);
    }

    // 在 NewsCategory 模型中添加以下方法
    public function news()
    {
        // 假設 News 模型中有一個 cat_id 外鍵，指向 NewsCategory
        return $this->hasMany(News::class, 'cat_id', 'cat_id');
    }

    // 在 News 模型中添加以下方法
    public function category()
    {
        // 假設 News 模型中的外鍵是 cat_id
        return $this->belongsTo(NewsCategory::class, 'cat_id', 'cat_id');
    }
}
