<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsDesc extends Model
{
    // 👉 指定資料表
    protected $table = 'news_desc';

    /**
     * ❗由於 news_desc 沒有 id，也沒有 auto-increment，
     *   只有 (news_id, lang_id) 當複合主鍵，
     *   Laravel 不能正式支援複合主鍵，
     *   所以要手動關閉 incrementing。
     */
    public $incrementing = false;

    // 👉 主鍵型態是 int（雖然是兩個 key，但 type 還是 int）
    protected $keyType = 'int';

    // 👉 可批量填入的欄位
    protected $fillable = [
        'news_id',
        'lang_id',
        'title',
        'description',
        'content'
    ];

    /**
     * 回到所屬的新聞主表
     * belongsTo(對方 model, 本表 news_id, 對方主鍵 news_id)
     */
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id', 'news_id');
    }

    public $timestamps = true; // 預設是 true，會自動管理 created_at 和 updated_at
}
