<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsDesc extends Model
{
    protected $table = 'news_desc';
    // 🔧 指定主鍵欄位名稱為 news_id
    protected $primaryKey = 'news_id';
    protected $fillable = ['news_id','lang_id','title','content'];
    public function news(){ return $this->belongsTo(News::class,'news_id'); }
}
