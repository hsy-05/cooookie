<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Schema;

trait HasImageFields
{
    /**
     * 泛用型刪除圖片處理邏輯
     */
    public function deleteImageFieldGeneric(Request $request, $model)
    {
        // 從前端傳來的欄位名稱 (例如：image_url)
        $field = $request->input('field');

        // 防呆：確保欄位名稱不是空的，且資料表真的有這個欄位
        if (!$field || !Schema::hasColumn($model->getTable(), $field)) {
            return response()->json(['success' => false, 'message' => '無效的欄位指定'], 400);
        }

        // 執行刪除：如果欄位有值，則調用 Helper 刪除檔案並將資料庫設為 null
        if ($model->$field) {
            ImageHelper::deleteImage($model->$field, 'public');
            $model->update([$field => null]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => '此圖片已被刪除或不存在'], 404);
    }
}
