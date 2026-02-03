<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable; // 引入 Trait

class News extends Model
{
    use Loggable; // 使用 Trait

    // 🔴 關閉自動監聽，改在 Controller 手動紀錄，確保標題正確
    public $enableAutoLog = false;

    // 定義 Log 顯示的模組名稱
    public $logName = '消息';

    // 告訴 Trait 標題要抓 'log_title' 這個屬性
    public $logTitle = 'log_title';

    // 指定操作的資料表名稱
    protected $table = 'news';

    // 指定主鍵欄位
    protected $primaryKey = 'news_id';

    // 主鍵是 int 並且是 auto-increment
    public $incrementing = true;

    // 主鍵的資料型態
    protected $keyType = 'int';

    /**
     * 控制可批量填入的欄位
     */
    protected $fillable = [
        'cat_id',
        'is_visible',
        'display_order',
        'image_url'
    ];

    /**
     * 一則資料會有多個語系描述
     */
    public function descs()
    {
        return $this->hasMany(NewsDesc::class, 'news_id', 'news_id');
    }

    /**
     * 取得目前語系的一筆描述資料
     * 當你要抓語系內容時可以直接使用：
     * $news->desc->title
     */
    public function desc()
    {
        $langId = session('lang_id') ?? 1;

        return $this->hasOne(NewsDesc::class, 'news_id', 'news_id')
            ->where('lang_id', $langId);
    }

    /**
     * 每則資料屬於某一個分類
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'cat_id', 'cat_id');
    }

    /**
     * !! 目前無使用此功能，但未來可能會用到 !!
     * 動態屬性：自動取得目前語系的標題
     * 讓你可以用 $news->title，而不是 $news->desc->title
     *
     */
    public function getTitleAttribute()
    {
        // 取得 app locale，例如 "zh-TW"
        $locale = app()->getLocale();

        // 靜態快取語系 ID，不用每次查資料庫
        static $langIdCache = null;
        if ($langIdCache === null) {
            $langIdCache = \App\Models\Language::where('code', $locale)->value('lang_id');
        }

        // 回傳符合語系的一筆 desc（效能最佳：使用 query，不抓整包 descs）
        return optional(
            $this->descs()->where('lang_id', $langIdCache)->first()
        )->title;
    }

    /**
     * 更新操作紀錄中的標題
     */
    public function getLogTitleAttribute()
    {
        // dd($this->desc);
        return $this->descs->first()->title ?? '未命名消息';
    }
}
