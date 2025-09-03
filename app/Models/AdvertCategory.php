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

    public function descs()
    {
        return $this->hasMany(AdvertCategoryDesc::class, 'cat_id');
    }

    // 加一個便捷函數取名稱
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
