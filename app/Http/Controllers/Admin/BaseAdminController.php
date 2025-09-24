<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // 引入 Request
use Illuminate\Support\Facades\Validator; // 引入 Validator
use Illuminate\Support\Facades\Log; // 引入 Log

class BaseAdminController extends Controller
{
    protected $pageTitle = '後台管理';

    /**
     * 統一輸出 view，並自動帶入 pageTitle
     */
    protected function view($view, $data = [])
    {
        return view($view, array_merge([
            'pageTitle' => $this->pageTitle,
        ], $data));
    }

    /**
     * 通用 AJAX 方法，用於切換模型中的布林值欄位 (例如 is_visible)。
     * 此方法放在 BaseAdminController 中，供所有後台控制器共用。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleBoolean(Request $request)
    {
        // 1. 驗證請求參數
        $validator = Validator::make($request->all(), [
            'model' => 'required|string', // 要更新的模型名稱 (例如 'Advert', 'News')
            'id' => 'required|integer',   // 要更新的記錄 ID
            'field' => 'required|string', // 要更新的布林值欄位名稱 (例如 'is_visible')
            'value' => 'required|boolean',// 要設定的新值 (true/false)
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '無效的請求參數。'], 400);
        }

        // 完整的模型類別名稱，確保模型在 App\Models 命名空間下
        $modelName = 'App\\Models\\' . $request->input('model');
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        // 2. 檢查模型是否存在且有效
        if (!class_exists($modelName)) {
            return response()->json(['success' => false, 'message' => '模型不存在。'], 404);
        }

        $model = new $modelName;

        // 3. 查找記錄
        $record = $model->find($id);

        if (!$record) {
            return response()->json(['success' => false, 'message' => '記錄不存在。'], 404);
        }

        // 4. 檢查欄位是否存在於模型中
        // 這裡使用 array_key_exists 檢查屬性，更嚴謹的做法是檢查 fillable 或 guarded
        // 但對於 is_visible 這種常見欄位，直接檢查屬性通常足夠
        if (!array_key_exists($field, $record->getAttributes())) {
            return response()->json(['success' => false, 'message' => '欄位不存在或不允許更新。'], 400);
        }

        // 5. 更新欄位值
        try {
            $record->{$field} = $value;
            $record->save();
            return response()->json(['success' => true, 'message' => '狀態更新成功。']);
        } catch (\Exception $e) {
            // 記錄錯誤以便調試
            Log::error("Failed to toggle boolean field for model {$modelName} (ID: {$id}, Field: {$field}): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '狀態更新失敗。'], 500);
        }
    }
}
