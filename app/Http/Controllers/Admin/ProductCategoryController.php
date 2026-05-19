<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\ProductCategoryRequest; // 注意：複製後需更換 Request
use App\Models\{ProductCategory, ProductCategoryDesc}; // 注意：複製後需更換 Model
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper, SummernoteImageHelper};
use Illuminate\Database\Eloquent\Model;

/**
 * 分類管理通用控制器
 * 透過配置 protected 屬性，可快速複製為產品分類、文章分類等模組
 */
class ProductCategoryController extends BaseAdminController
{
    /**
     * 核心配置：複製到其他分類功能時，只需修改這裡
     */
    protected $permissionName = 'product_category';           // 權限代碼
    protected $modelClass     = ProductCategory::class;       // 主模型類別
    protected $descClass      = ProductCategoryDesc::class;   // 語系模型類別
    protected $routePrefix    = 'admin.product_category';     // 路由前綴
    protected $primaryKey     = 'cat_id';                  // 資料表主鍵
    protected $pageTitle      = '消息分類管理';             // 頁面標題
    protected $logModuleName  = '消息分類';                 // 日誌顯示名稱
    protected $configKey      = 'product';                    // 對應 config/site.php 中的層級設定 Key

    /**
     * 圖片規格配置
     */
    protected $pageCfg = [
        'files' => [
            'image_url' => [
                'path'   => 'product_category',      // 圖片存儲資料夾
                'width'  => 736,                 // 建議寬度
                'height' => 736,                 // 建議高度
                'mode'   => 'scale_fill',        // 處理模式：等比例填充
                'bgColor'=> '#ffffff',           // 若圖片比例不符，填充的底色
                'useOriginalName' => false,      // 是否使用原檔名
            ],
        ],
    ];

    /**
     * 顯示分類列表頁面
     * 支援關鍵字搜尋與樹狀結構顯示
     *
     * @param Request $request 包含 'search' 參數
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 使用 Eager Loading 載入多語系與子分類，優化資料庫查詢效能
        $query = $this->modelClass::with(['children.descs', 'descs']);

        if ($search) {
            // 搜尋模式：列出所有符合名稱的分類（不分層級，方便查找）
            $catItems = $query->whereHas('descs', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->get();
        } else {
            // 標準模式：僅抓取第一層，透過 Model 關聯遞迴顯示子分類
            $catItems = $query->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('display_order', 'asc')
            ->orderBy($this->primaryKey, 'asc')
            ->get();
        }

        return $this->view("{$this->routePrefix}.index", compact('catItems', 'search'));
    }

    /**
     * 顯示新增表單
     */
    public function create()
    {
        SummernoteImageHelper::cleanAbandonedImages();
        return $this->renderForm(new $this->modelClass);
    }

    /**
     * 儲存新增資料
     *
     * @param ProductCategoryRequest $request 表單驗證物件
     */
    public function store(ProductCategoryRequest $request)
    {
        // 層級深度檢查，防止無限層級破壞版面
        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request) {
            try {
                $item = new $this->modelClass;
                $item->fill($request->safe()->except(['image_url']));

                // 處理封面圖上傳
                $this->handleFileUploads($request, $item);

                $item->parent_id = $request->parent_id ?: null;
                $item->is_visible = $request->has('is_visible');
                $item->save();

                // 確認編輯器中的暫存圖轉為正式檔案
                $editorId = $request->input('editor_id', 'default');
                SummernoteImageHelper::commitTempImages($editorId);

                // 儲存多語系描述
                $this->saveTranslations($item, $request->desc);

                $item->writeLog('新增', $item->currentDesc->name ?? "未知名{$this->logModuleName}");

                $backUrl = $request->input('back_url', route("{$this->routePrefix}.index"));

                $this->showMsg(0, "{$this->logModuleName}新增完成", [
                    ['text' => '繼續新增', 'href' => route("{$this->routePrefix}.create")],
                    ['text' => '繼續編輯', 'href' => route("{$this->routePrefix}.edit", $item->{$this->primaryKey})],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("{$this->logModuleName} Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '新增失敗：' . $e->getMessage());
            }
        });
    }

    /**
     * 顯示編輯頁面
     *
     * @param mixed $id 路由傳入的 ID
     */
    public function edit($id)
    {
        // 解決報錯關鍵：不使用型別提示，改用 ID 查詢物件
        $item = $this->modelClass::findOrFail($id);

        SummernoteImageHelper::cleanAbandonedImages();
        return $this->renderForm($item);
    }

    /**
     * 更新資料
     *
     * @param ProductCategoryRequest $request
     * @param mixed $id 路由傳入的 ID
     */
    public function update(ProductCategoryRequest $request, $id)
    {
        $item = $this->modelClass::findOrFail($id);

        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request, $item) {
            try {
                $item->fill($request->safe()->except(['image_url']));
                $item->parent_id = $request->parent_id ?: null;
                $item->is_visible = $request->has('is_visible');

                // 處理更新上傳 (會自動清理舊圖)
                $this->handleFileUploads($request, $item);
                $item->save();

                // 更新多語系
                $this->saveTranslations($item, $request->desc);
                // 寫入日誌
                $item->writeLog('編輯', $item->currentDesc->name ?? "未知名{$this->logModuleName}");

                $backUrl = $request->input('back_url', route("{$this->routePrefix}.index"));

                $this->showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route("{$this->routePrefix}.edit", $item->{$this->primaryKey})],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("{$this->logModuleName} Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '更新失敗');
            }
        });
    }

    /**
     * 刪除資料
     *
     * @param mixed $id 路由傳入的 ID
     */
    public function destroy($id)
    {
        $item = $this->modelClass::findOrFail($id);

        if ($item->items()->exists()) {
            return back()->with('form_error_swal', "此分類已有內容使用，無法刪除。");
        }

        // 檢查是否還有子分類
        if ($item->children()->exists()) {
            return back()->with('form_error_swal', '請先刪除底下的子分類。');
        }

        $title = $item->currentDesc->name ?? "未知名{$this->logModuleName}";

        foreach ($item->descs as $desc) {
            SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($desc->content), null);
        }

        $item->delete();
        $item->writeLog('刪除', $title);

        return redirect()->route("{$this->routePrefix}.index")->with('form_success_swal', "{$this->logModuleName}已刪除");
    }

    /**
     * AJAX 刪除單個圖片
     *
     * @param Request $request
     * @param mixed $id 路由傳入的 ID
     */
    public function deleteImageField(Request $request, $id)
    {
        $item = $this->modelClass::findOrFail($id);
        // 使用 BaseAdminController 中 HasImageFields Trait 的方法
        return $item->deleteImageFieldGeneric($request);
    }

    /* -------------------------------------------------------------------------- */
    /* 內部輔助方法 (符合專業開發範式)                                              */
    /* -------------------------------------------------------------------------- */

    /**
     * 渲染表單邏輯
     *
     * @param mixed $item
     */
    private function renderForm($item)
    {
        $isEdit = (bool)$item->exists;
        $maxLevel = config("site.category_levels.{$this->configKey}", 1);

        // 取得根分類準備建構下拉樹狀選單
        $rootCategories = $this->modelClass::with(['children', 'descs'])
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('display_order', 'asc')
            ->get();

        $parentsList = [];
        // 遍歷根分類，透過遞迴函式去計算每個分類的「縮排」與「是否可當父層」
        foreach ($rootCategories as $root) {
            $this->buildTreeOptions($root, 0, $parentsList, $item->{$this->primaryKey}, $maxLevel);
        }

        // 【防呆掃除】進入頁面時，把上次「沒存檔就關掉」的圖片清空
        SummernoteImageHelper::cleanAbandonedImages();

        // 獲取目前啟用的語系設定
        $langs = $this->getActiveLanguages();
        // 將配置傳給前端，以便顯示建議尺寸提示
        $fileConfigs = $this->pageCfg['files'];

        // 語系資料 Map (方便 View 直接透過 $descMap[$lang_id] 取值)
        $descMap = [];
        if ($isEdit) {
            $item->load('descs');
            foreach ($item->descs as $desc) {
                // 編輯器內容需要解碼回原始 URL，才能正確顯示圖片
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        // 預設返回按鈕路由
        $backUrl = $this->getBackUrl("{$this->routePrefix}.index");

        return $this->view("{$this->routePrefix}.form", [
            'category'    => $item,
            'isEdit'      => $isEdit,
            'parentsList' => $parentsList,
            'langs'       => $langs,
            'descMap'     => $descMap,
            'fileConfigs' => $fileConfigs,
            'maxLevel'    => $maxLevel,
            'backUrl'     => $backUrl
        ]);
    }

    /**
     * 遞迴計算分類樹狀結構與縮排
     * @param ProductCategory $category  當前跑到的分類
     * @param int          $level     當前層級深度
     * @param array        &$result   結果儲存陣列
     * @param int|null     $currentId 目前編輯的 ID (用來排除自己不能選自己)
     * @param int          $maxLevel  系統允許的最大深度
     */
    private function buildTreeOptions($category, $level, &$result, $currentId, $maxLevel)
    {
        // 排除自己與自己的後代當作父層，避免無窮迴圈
        if ($category->{$this->primaryKey} == $currentId) return;

        $canBeParent = ($level + 1) < $maxLevel;
        $name = $category->currentDesc->name ?? '未命名';
        $indent = $level > 0 ? str_repeat('　', $level) . '└─ ' : '';

        $result[] = (object)[
            'cat_id'        => $category->{$this->primaryKey},
            'name'          => $indent . $name,
            'can_be_parent' => $canBeParent
        ];

        if ($category->children->count() > 0) {
            foreach ($category->children as $child) {
                $this->buildTreeOptions($child, $level + 1, $result, $currentId, $maxLevel);
            }
        }
    }

    /**
     * 檢查層級深度是否合規
     *
     * @param int|null $parentId 父層ID
     */
    private function checkLevelLimit($parentId)
    {
        // 如果是設為第一層，就不需要檢查深度
        if (empty($parentId) || $parentId == 0) {
            return null;
        }

        $parent = $this->modelClass::find($parentId);
        if (!$parent) return redirect()->back()->with('form_error_swal', '找不到指定的父分類');

        // 往上追溯計算目前深度
        $depth = 1;
        $temp = $parent;
        while ($temp->parent_id > 0) {
            $temp = $this->modelClass::find($temp->parent_id);
            if (!$temp) break;
            $depth++;
        }

        $maxLimit = config("site.category_levels.{$this->configKey}", 1);

        // 如果「父層深度 + 我自己這一層」超過限制
        if (($depth + 1) > $maxLimit) {
            $this->showMsg(1, "層級過深：此單元最高僅允許 {$maxLimit} 層", [['text' => '返回', 'href' => url()->previous()]], true);
            return redirect()->back();
        }

        // 表示沒問題
        return null;
    }

    /**
     * 處理檔案上傳
     */
    private function handleFileUploads(Request $request, $item)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {
                $item->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $item->$field,
                    $config
                );
            }
        }
    }

    /**
     * 儲存多語系資料
     */
    private function saveTranslations($item, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            // 如果名稱為空，視為刪除該語系內容
            if (empty($data['name'])) {
                // 刪除前，先抓出舊內容，把裡面的圖片也清掉，避免佔用空間
                $oldDesc = $this->descClass::where($this->primaryKey, $item->{$this->primaryKey})->where('lang_id', $langId)->first();
                if ($oldDesc) {
                    SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($oldDesc->content), null);
                    $oldDesc->delete();
                }
                continue;
            }

            $this->descClass::updateOrInsert(
                [$this->primaryKey => $item->{$this->primaryKey}, 'lang_id' => $langId],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
                ]
            );
        }
    }
}
