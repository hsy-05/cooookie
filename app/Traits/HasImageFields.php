<?php

namespace App\Traits;

use App\Helpers\ImageHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * 圖片欄位處理 Trait
 *
 * 提供：
 * - 刪除資料時自動刪除圖片
 * - 手動刪除指定圖片欄位
 * - AJAX 刪除圖片功能
 *
 * 注意：
 * 此 Trait 必須搭配 Eloquent Model 使用。
 *
 * @mixin Model
 */
trait HasImageFields
{
    /**
     * 初始化 Trait 的 Model 事件
     *
     * 當 Model 被刪除時，
     * 自動清除對應的圖片檔案。
     *
     * @return void
     */
    protected static function bootHasImageFields(): void
    {
        static::deleted(function ($model) {

            // 取得所有圖片欄位
            foreach ($model->getImageFields() as $field) {

                // 防呆：避免空值
                if (!empty($model->$field)) {

                    // 刪除實體圖片
                    ImageHelper::deleteImage($model->$field);
                }
            }
        });
    }

    /**
     * 取得 Model 定義的圖片欄位
     *
     * Model 可自行定義：
     *
     * protected array $imageFields = [
     *     'image',
     *     'banner_image'
     * ];
     *
     * @return array 圖片欄位名稱陣列
     */
    public function getImageFields(): array
    {
        return property_exists($this, 'imageFields')
            ? $this->imageFields
            : [];
    }

    /**
     * 刪除指定欄位的圖片
     *
     * 功能：
     * - 刪除 storage 圖片
     * - 將資料庫欄位設為 null
     *
     * @param string $field 欲刪除的圖片欄位名稱
     * @return bool 是否刪除成功
     */
    public function removeImageFromField(string $field): bool
    {
        // 防呆：欄位不存在
        if (!isset($this->$field)) {
            return false;
        }

        // 防呆：欄位為空
        if (empty($this->$field)) {
            return false;
        }

        // 刪除圖片檔案
        ImageHelper::deleteImage($this->$field);

        // 更新資料庫欄位
        return $this->update([
            $field => null
        ]);
    }

    /**
     * 提供 AJAX 呼叫的泛用圖片刪除功能
     *
     * 前端可傳入：
     * field=image
     *
     * 即可刪除對應欄位圖片。
     *
     * @param Request $request HTTP 請求資料
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImageFieldGeneric(Request $request)
    {
        $field = $request->input('field');

        // 防呆：未傳欄位名稱
        if (!$field) {
            return response()->json([
                'success' => false,
                'message' => '未指定欄位名稱'
            ], 400);
        }

        // 執行圖片刪除
        if ($this->removeImageFromField($field)) {

            return response()->json([
                'success' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '圖片刪除失敗'
        ], 404);
    }
}
