<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTagFields; // 引入特徵

class NewsDesc extends Model
{
    use HasTagFields; // 引入自動標籤轉換功能

    protected $table = 'news_desc';

    /**
     * ❗由於 news_desc 沒有 id，也沒有 auto-increment，
     *   只有 (news_id, lang_id) 當複合主鍵，
     *   Laravel 不能正式支援複合主鍵，
     *   所以要手動關閉 incrementing。
     */
    public $incrementing = false;

    // 不使用自動維護的時間戳記
    public $timestamps = false;

    // 👉 主鍵型態是 int（雖然是兩個 key，但 type 還是 int）
    protected $keyType = 'int';

    /**
     * 標籤欄位白名單
     * 這是 HasTagFields 特徵的控制開關。
     * 未來若有新的欄位也需要「自動轉陣列」，只需將欄位名加進此陣列即可，不需額外寫邏輯。
     */
    protected $tagFields = ['meta_keyword'];

    protected $fillable = [
        'news_id',
        'lang_id',
        'title',
        'description',
        'content',
        'meta_title',
        'meta_keyword',    // ✅ 必須加上這一行，否則資料存不進去
        'meta_description',
        'seo_h1'
    ];

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id', 'news_id');
    }
}
