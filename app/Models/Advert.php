<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable; // 引入 Trait

class Advert extends Model
{
    use Loggable; // 使用 Trait

    // 🔴 關閉自動監聽，改在 Controller 手動紀錄，確保標題正確
    public $enableAutoLog = false;

    // 定義 Log 顯示的模組名稱
    public $logName = '廣告';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'advert';

    // 指定主鍵欄位
    protected $primaryKey = 'adv_id';

    // 主鍵是 int 並且是 auto-increment
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

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

    /**
     * 一則資料會有多個語系描述
     */
    public function descs()
    {
        return $this->hasMany(AdvertDesc::class, 'adv_id', 'adv_id');
    }

    /**
     * 取得目前語系的一筆描述資料
     * 當你要抓語系內容時可以直接使用：
     * $advert->desc->title
     */
    public function desc()
    {
        $langId = session('lang_id') ?? 1;

        return $this->hasOne(AdvertDesc::class, 'adv_id', 'adv_id')
            ->where('lang_id', $langId);
    }

    /** 所屬分類 */
    public function category()
    {
        return $this->belongsTo(AdvertCategory::class, 'cat_id');
    }

    /**
     * 更新操作紀錄中的標題
     */
    public function getLogTitleAttribute()
    {
        return $this->descs->first()->title ?? '未命名廣告';
    }
}
