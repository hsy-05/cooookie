<?php

namespace App\Traits;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait HasImageFields
{
    /**
     * 自動初始化 HasImageFields Trait 的事件監聽。
     */
    protected static function bootHasImageFields()
    {
        // 當整筆資料被刪除時，自動把對應的實體圖檔也刪掉
        static::deleted(function ($model) {
            foreach ($model->getImageFields() as $field) {
                if (!empty($model->$field)) {
                    ImageHelper::deleteImage($model->$field);
                }
            }
        });
    }

    /**
     * 定義哪些欄位是圖片欄位。
     * 使用此 Trait 的 Model 可以覆寫這個屬性。
     * 例如：protected array $imageFields = ['avatar_url', 'cover_image'];
     */
    public function getImageFields(): array
    {
        return property_exists($this, 'imageFields') ? $this->imageFields : [];
    }

    /**
     * 刪除 Model 上指定的圖片欄位 (不綁定 HTTP Request，更通用)
     *
     * @param string $field 欄位名稱
     * @return bool 是否成功刪除
     */
    public function removeImageFromField(string $field): bool
    {
        // 防呆：確保欄位存在且有值
        if (!Schema::hasColumn($this->getTable(), $field) || empty($this->$field)) {
            return false;
        }

        // 刪除實體檔案
        ImageHelper::deleteImage($this->$field);

        // 將資料庫該欄位設為 null
        return $this->update([$field => null]);
    }

    /**
     * [保留原功能供 AJAX 呼叫] 泛用型刪除圖片處理邏輯
     */
    public function deleteImageFieldGeneric(Request $request)
    {
        $field = $request->input('field');

        if (!$field) {
            return response()->json(['success' => false, 'message' => '未指定欄位名稱'], 400);
        }

        if ($this->removeImageFromField($field)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => '圖片刪除失敗，欄位不存在或檔案已遺失'], 404);
    }
}
