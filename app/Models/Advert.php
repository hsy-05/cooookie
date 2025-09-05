<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    protected $table = 'advert';
    protected $primaryKey = 'adv_id';

    protected $fillable = [
        'cat_id',
        'adv_img_url',
        'adv_img_m_url',
        'adv_link_url',
        'display_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /** 多語系描述 */
    public function descs()
    {
        return $this->hasMany(AdvertDesc::class, 'adv_id');
    }

    /** 所屬分類 */
    public function category()
    {
        return $this->belongsTo(AdvertCategory::class, 'cat_id');
    }
}

class AdvertDesc extends Model
{
    protected $table = 'advert_desc';

    protected $fillable = [
        'adv_id',
        'lang_id',
        'adv_name',
        'adv_subname',
        'adv_brief'
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'adv_id');
    }
}
