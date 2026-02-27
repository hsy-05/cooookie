<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;       // 引入操作紀錄
use App\Traits\HasImageFields; // 引入圖片處理（保持架構統一）

class AdvertCategory extends Model
{
    use Loggable, HasImageFields;

    protected $table = 'advert_category';
    protected $primaryKey = 'cat_id';

    // 定義 Log 顯示的模組名稱
    public $logName = '廣告分類';

    protected $fillable = [
        'cat_code',
        'cat_func_scope',
        'cat_params',
        'display_order',
        'is_visible',
    ];

    /**
     * 自動轉型設定
     * 將資料庫的 JSON 格式自動轉為 PHP 陣列，這讓開發時不用手動 json_decode
     */
    protected $casts = [
        'cat_func_scope' => 'array',
        'cat_params'     => 'array',
        'is_visible'     => 'boolean',
    ];

    /**
     * 關聯：所有語系描述
     */
    public function descs()
    {
        return $this->hasMany(AdvertCategoryDesc::class, 'cat_id', 'cat_id');
    }

    /**
     * 關聯：目前語系的描述 (用於列表顯示)
     */
    public function desc()
    {
        $langId = session('lang_id') ?? 1;
        return $this->hasOne(AdvertCategoryDesc::class, 'cat_id', 'cat_id')
            ->where('lang_id', $langId);
    }

    /**
     * 關聯：此分類下的所有廣告內容
     */
    public function adverts()
    {
        return $this->hasMany(Advert::class, 'cat_id', 'cat_id');
    }

    /* --- Accessors (存取器) --- */

    /**
     * 萬用標題獲取器：$category->title
     * 優先抓目前語系，沒抓到就抓第一個，再沒有就抓代碼。
     */
    public function getTitleAttribute()
    {
        // 如果已經 eager load 了 desc，就從裡面拿，避免重複查資料庫
        return $this->desc->cat_name ?? ($this->descs->first()->cat_name ?? '未命名分類');
    }
}
