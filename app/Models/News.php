<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    // 🔧 指定主鍵欄位名稱為 news_id
    protected $primaryKey = 'news_id';

    protected $fillable = ['cat_id', 'is_visible', 'display_order', 'image'];

    public function descs()
    {
        return $this->hasMany(NewsDesc::class, 'news_id');
    }

    public function desc()
    {
        return $this->hasOne(NewsDesc::class, 'news_id')->where('lang_id', session('lang_id') ?? 1);
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'cat_id');
    }

    /**
     * 取得目前語系的標題
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();

        static $langIdCache = null;
        if ($langIdCache === null) {
            $langIdCache = \App\Models\Language::where('code', $locale)->value('lang_id');
        }

        return optional($this->descs->firstWhere('lang_id', $langIdCache))->title;
    }
}

class NewsDesc extends Model
{
    protected $table = 'news_desc';
    // 指定主鍵欄位名稱為 news_id
    protected $primaryKey = 'news_id';
    protected $fillable = ['news_id', 'lang_id', 'title', 'content'];
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
