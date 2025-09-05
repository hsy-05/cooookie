<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertCategory extends Model
{
    protected $table = 'advert_category';
    protected $primaryKey = 'cat_id';

    protected $fillable = [
        'cat_code',
        'cat_func_scope',
        'cat_params',
        'display_order',
        'is_visible',
    ];

    protected $casts = [
        'cat_func_scope' => 'array',
        'cat_params'     => 'array',
        'is_visible'     => 'boolean',
    ];

    /** 多筆語系描述 */
    public function descs()
    {
        return $this->hasMany(AdvertCategoryDesc::class, 'cat_id');
    }

    /** 分類下的所有廣告 */
    public function adverts()
    {
        return $this->hasMany(Advert::class, 'adv_id');
    }

    /** 便捷函式：取第一個語系名稱 */
    public function name()
    {
        return $this->descs()->first()->cat_name ?? null;
    }
}

class AdvertCategoryDesc extends Model
{
    protected $table = 'advert_category_desc';

    protected $fillable = [
        'cat_id',
        'lang_id',
        'cat_name',
    ];

    public function category()
    {
        return $this->belongsTo(AdvertCategory::class, 'cat_id');
    }
}
