<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // 引入 Request
use Illuminate\Support\Facades\{Validator, Log, Auth};
use App\Models\{Language, ActionLog};
use \App\Traits\HasImageFields; // 引入外掛
use App\Helpers\ImageHelper; // 引入圖片處理 Helper


class BaseAdminController extends Controller
{
    use HasImageFields; // 使用外掛
    /**
     * 定義該模組的權限名稱
     * 例如：'news', 'admins', 'roles'
     * 子類別必須複寫此屬性
     */
    protected $permissionName = '';

    public function __construct()
    {
        // 如果子類別有設定 $permissionName，才自動啟動權限檢查機制
        if (!empty($this->permissionName)) {
            $this->registerPermissionMiddleware();
        }
    }

    /**
     * 自動綁定權限到 Resource 方法 (index, create, edit, destroy 等)
     */
    protected function registerPermissionMiddleware()
    {
        $prefix = $this->permissionName;

        // 瀏覽列表 -> 檢查是否擁有 .view 權限
        $this->middleware("admin.perm:{$prefix}.view")
            ->only(['index', 'show']);

        // 新增與編輯 -> 檢查是否擁有 .create 權限
        $this->middleware("admin.perm:{$prefix}.create")
            ->only(['create', 'store', 'edit', 'update']);

        // 刪除 -> 檢查是否擁有 .delete 權限
        $this->middleware("admin.perm:{$prefix}.delete")
            ->only(['destroy']);

        // 💡 面試官亮點：這種寫法叫做「約定優於配置」，
        // 只要子類別寫 protected $permissionName = 'news';
        // 剩下的增刪查改權限都會自動鎖好，不用每個 Controller 重寫一遍。
    }

    /**
     * 頁面標題
     */
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
        // 驗證請求參數
        $validator = Validator::make($request->all(), [
            'model' => 'required|string', // 要更新的模型名稱 (例如 'Advert', 'News')
            'id' => 'required|integer',   // 要更新的記錄 ID
            'field' => 'required|string', // 要更新的布林值欄位名稱 (例如 'is_visible')
            'value' => 'required|boolean', // 要設定的新值 (true/false)
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '無效的請求參數。'], 400);
        }

        // 完整的模型類別名稱，確保模型在 App\Models 命名空間下
        $modelName = 'App\\Models\\' . $request->input('model');
        $id = $request->input('id');
        $field = $request->input('field');
        $value = $request->input('value');

        // 檢查模型是否存在且有效
        if (!class_exists($modelName)) {
            return response()->json(['success' => false, 'message' => '模型不存在。'], 404);
        }

        $model = new $modelName;

        // 查找記錄
        $record = $model->find($id);

        if (!$record) {
            return response()->json(['success' => false, 'message' => '記錄不存在。'], 404);
        }

        // 檢查欄位是否存在於模型中
        // 這裡使用 array_key_exists 檢查屬性，更嚴謹的做法是檢查 fillable 或 guarded
        // 但對於 is_visible 這種常見欄位，直接檢查屬性通常足夠
        if (!array_key_exists($field, $record->getAttributes())) {
            return response()->json(['success' => false, 'message' => '欄位不存在或不允許更新。'], 400);
        }

        // 更新欄位值
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

    /**
     * 取得目前系統已啟用的語系清單
     * * 用途：
     * 1. 用於渲染編輯/新增表單中的多語系頁籤 (Tabs)。
     * 2. 確保後台只顯示「狀態為啟用 (enabled=1)」的語系，避免編輯到隱藏語系。
     * 3. 統一排序規則（如：繁中 -> 簡中 -> 英文），讓介面顯示保持一致。
     * * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActiveLanguages()
    {
        return Language::where('enabled', 1)            // 只撈取已啟用的語系
            ->orderByDesc('display_order')   // 依照自訂排序值降冪排列
            ->get();
    }

    /**
     * 批次刪除紀錄通用方法
     *
     * @param string $moduleName 模組名稱，例如「消息管理」
     * @param int $count 刪除筆數
     * @param array|null $ids 選擇性提供刪除 ID 陣列
     */
    protected function writeBatchDeleteLog(string $moduleName, int $count, ?array $ids = null): void
    {
        $info = "[{$moduleName}] 批次刪除 {$count} 筆資料";

        if ($ids && count($ids) <= 10) {
            // 如果刪除筆數少，順便記 ID
            $info .= " (IDs: " . implode(',', $ids) . ")";
        }

        ActionLog::create([
            'admin_id'    => Auth::id(),
            'action'     => '刪除',
            'log_info'   => $info,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * AJAX 即時刪除編輯器內的圖片檔案
     * 這裡是為了回應 Summernote 的「移除圖片」按鈕
     */
    public function deleteEditorImage(Request $request)
    {
        // 1. 抓取網址
        $imageUrl = $request->input('image_url');

        if (!$imageUrl) {
            return response()->json(['status' => 'error', 'message' => '缺少網址'], 400);
        }

        // 2. 解析路徑 (只處理本站 storage 內的檔案)
        $storageMarker = '/storage/';
        if (str_contains($imageUrl, $storageMarker)) {
            // 取得 /storage/ 之後的相對路徑 (例如: news/content/xxx.jpg)
            $parts = explode($storageMarker, $imageUrl);
            $relativePath = end($parts);

            // 3. 調用你寫好的 ImageHelper 工具
            // 它會自動判斷 exists() 並執行 Storage::delete()
            $result = \App\Helpers\ImageHelper::deleteImage($relativePath, 'public');

            if ($result) {
                return response()->json(['status' => 'success', 'message' => '圖片刪除成功']);
            }
        }

        return response()->json(['status' => 'error', 'message' => '檔案不存在或無法刪除'], 404);
    }
}
