<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, Log, Auth, Schema, Route};
use App\Models\{Language, ActionLog};
use App\Traits\HasImageFields;
use App\Helpers\ImageHelper;

/**
 * 後台基礎控制器
 * 所有的後台 Controller 都必須繼承這一個類別，確保權限、分頁、日誌與「頁面標題自動化」邏輯統一。
 */
class BaseAdminController extends Controller
{
    use HasImageFields;

    /**
     * 定義該模組的權限代碼（需對應 backend_permissions.php 中的子功能 key）
     * 系統會根據此代碼自動從權限設定檔中抓取「模組名稱」來組成頁面標題。
     * @var string
     */
    protected $permissionName = '';

    /**
     * 頁面主標題（通常是「網站管理員」或「廣告管理」）
     * @var string
     */
    protected $pageTitle = '後台管理';

    /**
     * 每頁顯示筆數的參數設定
     */
    protected $perPageLimit = 100;
    protected $defaultPerPage = 10;

    /**
     * 控制器建構子
     */
    public function __construct()
    {
        // 只有在定義了權限名稱的情況下，才自動掛載權限判斷中間件與自動化標題解析
        if (!empty($this->permissionName)) {
            $this->registerPermissionMiddleware();
            $this->autoGeneratePageTitle();
        }
    }
    /**
     * 自動產生頁面標題邏輯
     * 用途：從 config\backend_permissions.php 自動抓取對應的中文標籤
     */
    protected function autoGeneratePageTitle(): void
    {
        $allPermissions = config('backend_permissions');

        if (!$allPermissions) return;

        foreach ($allPermissions as $group) {
            if (isset($group['subs'][$this->permissionName])) {
                $subLabel = $group['subs'][$this->permissionName]['label'];

                // [專業調整]：將子功能（最新消息）放前面，大分類（消息管理）放後面
                // 這樣瀏覽器標籤會顯示：最新消息 - 消息管理 - XXX有限公司
                $this->pageTitle = $subLabel . ' - ' . $group['label'];
                break;
            }
        }
    }

    /**
     * 權限中間件自動綁定
     * 讓開發者只需要宣告 $permissionName，不用在每個方法手動寫 can(...) 判斷
     */
    protected function registerPermissionMiddleware(): void
    {
        $prefix = $this->permissionName;

        // 讀取權限：對應 index 與 show 方法
        $this->middleware("admin.perm:{$prefix}.view")
            ->only(['index', 'show']);

        // 寫入權限：對應新增與編輯相關的所有方法
        $this->middleware("admin.perm:{$prefix}.create")
            ->only(['create', 'store', 'edit', 'update']);

        // 刪除權限：對應銷毀方法
        $this->middleware("admin.perm:{$prefix}.delete")
            ->only(['destroy']);
    }

    /**
     * 統一渲染視圖的方法
     * 用途：自動將標題帶入前端，減少在子類別重複寫 compact 的頻率
     * * @param string $view 視圖路徑 (例如 'admin.admins.index')
     * @param array $data 傳遞給前端的資料陣列
     * @return \Illuminate\View\View
     */
    protected function view($view, $data = [])
    {
        // 防呆：取得目前執行的 Method 名稱（如 index, create, edit）
        $currentMethod = Route::current() ? Route::current()->getActionMethod() : '';

        // 【專業優化】預先處理標題資訊，避免 Blade 重複拆解字串
        $titleParts = explode(' - ', $this->pageTitle);
        $titleConfig = [
            'full'  => $this->pageTitle,           // 完整標題 (用於瀏覽器 title)
            'main'  => $titleParts[0] ?? '',       // 主標題 (例如：最新消息)
            'group' => $titleParts[1] ?? '',       // 群組名 (例如：消息管理)
        ];

        // 如果是編輯或新增頁面，可以動態加上標記（目前註解備用）
        // if (in_array($currentMethod, ['create', 'edit'])) {
        //     $titleConfig['main'] .= ' (校稿)';
        // }

        return view($view, array_merge([
            'pageTitle'   => $this->pageTitle,      // 舊有變數相容性
            'titleConfig' => $titleConfig,          // 新型態標題物件
            'permissionName' => $this->permissionName, // 自動把權限代碼傳給前端
        ], $data));
    }

    /**
     * 取得目前分頁筆數（具備記憶功能）
     * 邏輯：優先抓 URL 參數 -> 再來是 Session -> 最後是預設值
     * * @param Request $request
     * @return int
     */
    public function getPerPage(Request $request): int
    {
        if ($request->has('per_page')) {
            $perPage = (int) $request->input('per_page');

            // 防呆：確保數值在合理範圍內 (1 ~ 100)
            if ($perPage < 1) $perPage = $this->defaultPerPage;
            if ($perPage > $this->perPageLimit) $perPage = $this->perPageLimit;

            session(['admin_per_page' => $perPage]);
            return $perPage;
        }

        // 沒帶參數就從 Session 抓取，再沒有就給預設值
        return (int) session('admin_per_page', $this->defaultPerPage);
    }

    /**
     * AJAX 快速切換開關 (例如：顯示狀態、推薦狀態)
     * 具備動態檢查欄位與 Model 安全驗證的功能
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleBoolean(Request $request)
    {
        // 基礎參數驗證
        $validator = Validator::make($request->all(), [
            'model' => 'required|string',
            'id'    => 'required|integer',
            'field' => 'required|string',
            'value' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => '請求格式錯誤'], 400);
        }

        // 安全白名單：只有這些欄位允許快速切換，防止惡意修改其他敏感欄位
        $allowedFields = ['is_visible', 'is_active', 'is_top', 'is_hot', 'enabled'];
        if (!in_array($request->field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => '此欄位禁止快速變更'], 403);
        }

        $modelName = 'App\\Models\\' . $request->input('model');
        $field     = $request->input('field');

        // 安全防呆：檢查資料模型是否存在
        if (!class_exists($modelName)) {
            return response()->json(['success' => false, 'message' => '系統找不到指定的資料模型'], 404);
        }

        $record = $modelName::find($request->input('id'));
        if (!$record) {
            return response()->json(['success' => false, 'message' => '找不到該筆資料紀錄'], 404);
        }

        // 安全防呆：檢查資料表是否真的有這個欄位
        if (!Schema::hasColumn($record->getTable(), $field)) {
            return response()->json(['success' => false, 'message' => '資料表無此欄位，操作已拒絕'], 400);
        }

        try {
            $record->{$field} = $request->input('value');
            $record->save();
            return response()->json(['success' => true, 'message' => '狀態切換完成']);
        } catch (\Exception $e) {
            Log::error("切換開關發生異常: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '更新失敗，請檢查權限或資料格式'], 500);
        }
    }

    /**
     * 獲取目前啟用的語言清單
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActiveLanguages()
    {
        return Language::where('enabled', 1)
            ->orderByDesc('display_order')
            ->get();
    }

    /**
     * 紀錄批次刪除的操作日誌
     *
     * @param string $moduleName 模組中文名稱
     * @param int $count 刪除數量
     * @param array|null $ids 被刪除的紀錄 ID 清單
     */
    protected function writeBatchDeleteLog(string $moduleName, int $count, ?array $ids = null): void
    {
        $logInfo = "[{$moduleName}] 執行批次刪除，共 {$count} 筆。";

        // 當 ID 數量不多時，紀錄具體的 ID 供日後追查
        if ($ids && count($ids) <= 20) {
            $logInfo .= " 詳細 ID: " . implode(', ', $ids);
        }

        ActionLog::create([
            'admin_id'   => Auth::id(),
            'action'     => '刪除',
            'log_info'   => $logInfo,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * 刪除編輯器內容中的圖片
     * 用於 Summernote 等編輯器在刪除圖片按鈕觸發時的後端處理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEditorImage(Request $request)
    {
        $imageUrl = $request->input('image_url');

        if (empty($imageUrl)) {
            return response()->json(['status' => 'error', 'message' => '未傳入圖片網址'], 400);
        }

        // 安全檢查：只允許處理本站儲存空間內的檔案
        $searchKey = '/storage/';
        if (!str_contains($imageUrl, $searchKey)) {
            return response()->json(['status' => 'error', 'message' => '非本站檔案，拒絕操作'], 403);
        }

        // 解析相對路徑
        $pathParts = explode($searchKey, $imageUrl);
        $relativePath = end($pathParts);

        // 安全防呆：防止路徑穿越攻擊 (Directory Traversal)
        if (str_contains($relativePath, '..')) {
            return response()->json(['status' => 'error', 'message' => '非法路徑請求'], 403);
        }

        // 執行實體檔案刪除
        if (ImageHelper::deleteImage($relativePath, 'public')) {
            return response()->json(['status' => 'success', 'message' => '伺服器端檔案已同步移除']);
        }

        return response()->json(['status' => 'error', 'message' => '檔案不存在或已提前移除'], 404);
    }
}
