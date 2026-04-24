<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\NewsCategoryRequest;
use App\Models\{NewsCategory, NewsCategoryDesc};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper, SummernoteImageHelper};

/**
 * 消息分類管理控制器
 * 採用 BaseAdminController 繼承架構，實現自動權限控管與共用功能
 */
class NewsCategoryController extends BaseAdminController
{
    // 定義權限與標題，BaseAdminController 會自動處理權限檢查
    protected $permissionName = 'news_category';
    protected $pageTitle = '消息分類管理';

    /**
     * 頁面相關配置
     * 讓開發者只需要改這裡的參數，就能控制圖片上傳規格，不需動到 store/update 邏輯
     */
    protected $pageCfg = [
        'files' => [
            'image_url' => [
                'path'   => 'news_category',      // 圖片存儲資料夾
                'width'  => 736,                 // 建議寬度
                'height' => 736,                 // 建議高度
                'mode'   => 'scale_fill',        // 處理模式：等比例填充
                'bgColor'=> '#D6395C',           // 若圖片比例不符，填充的底色
                'useOriginalName' => false,      // 是否使用原檔名
            ],
        ],
    ];

    /**
     * 顯示分類列表頁面
     * @param Request $request 包含搜尋關鍵字 'search'
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 使用 Eager Loading 載入多語系與子分類，避免 N+1 查詢效能問題
        $query = NewsCategory::with(['children.descs', 'descs']);

        if ($search) {
            // 搜尋模式：直接列出符合名稱的所有分類（打破樹狀結構，方便快速尋找）
            $categories = $query->whereHas('descs', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->get();
        } else {
            // 標準模式：顯示樹狀結構，只抓第一層 (parent_id 為 0 或 null)
            $categories = $query->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('cat_id', 'asc')
            ->get();
        }

        return $this->view('admin.news_category.index', compact('categories', 'search'));
    }

    /**
     * 顯示新增表單
     */
    public function create()
    {
        // 同步 News 規格：掃除編輯器廢棄圖
        SummernoteImageHelper::cleanAbandonedImages();

        return $this->renderForm(new NewsCategory());
    }

    /**
     * 儲存新增資料
     * @param NewsCategoryRequest $request 已經過表單驗證
     */
    public function store(NewsCategoryRequest $request)
    {
        // 1. 層級深度防呆：避免使用者建立超過系統限制的層級 (例如 3 層以上)
        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request) {
            try {
                $category = new NewsCategory();
                $category->fill($request->safe()->except(['image_url']));

                $this->handleFileUploads($request, $category);

                $category->parent_id = $request->parent_id ?: null;
                $category->is_visible = $request->has('is_visible');
                $category->save();

                // 【關鍵補強】存檔成功，告知 Helper 這個編輯器 ID 的圖片不用再被掃除
                $editorId = $request->input('editor_id', 'default');
                SummernoteImageHelper::commitTempImages($editorId);

                $this->saveTranslations($category, $request->desc);
                $category->writeLog('新增', $category->desc->name ?? '未知名分類');

                // 5. 寫入操作日誌 (Loggable Trait)
                $category->writeLog('新增', $category->desc->name ?? '未知名分類');

                $backUrl = $request->input('back_url', route('admin.news_category.index'));

                $this->showMsg(0, '分類新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.news_category.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '新增失敗：' . $e->getMessage());
            }
        });
    }

    /**
     * 顯示編輯表單
     * @param NewsCategory $category 自動透過 ID 注入的 Model
     */
    public function edit(NewsCategory $category)
    {
        // 同步 News 規格：掃除編輯器廢棄圖
        SummernoteImageHelper::cleanAbandonedImages();
        return $this->renderForm($category);
    }

    /**
     * 更新分類資料
     */
    public function update(NewsCategoryRequest $request, NewsCategory $category)
    {
        // 層級深度防呆
        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request, $category) {
            try {

                $category->fill($request->safe()->except(['image_url']));

                $category->parent_id = $request->parent_id ?: null;
                $category->is_visible = $request->has('is_visible');

                // 處理更新上傳 (會自動清理舊圖)
                $this->handleFileUploads($request, $category);

                $category->save();

                // 更新多語系
                $this->saveTranslations($category, $request->desc);

                // 寫入日誌
                $category->writeLog('編輯', $category->desc->name ?? '未知名分類');

                $backUrl = $request->input('back_url', route('admin.news_category.index'));

                $this->showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '更新失敗');
            }
        });
    }

    /**
     * 刪除分類 (包含嚴格防呆)
     */
    public function destroy(NewsCategory $category)
    {
        // 安全檢查 1：如果此分類下還有新聞稿，禁止刪除
        if ($category->news()->exists()) {
            return back()->with('form_error_swal', '此分類已有消息使用，無法刪除。');
        }

        // 安全檢查 2：如果此分類還有子分類，禁止刪除（避免斷頭資料）
        if ($category->children()->exists()) {
            return back()->with('form_error_swal', '請先刪除底下的子分類。');
        }

        $title = $category->desc->name ?? '未知名分類';

        // 刪除整筆資料時，也要清空所有語系編輯器內的圖片
        foreach ($category->descs as $desc) {
            SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($desc->content), null);
        }

        // 注意：這裡不再需要手動用 ImageHelper::deleteImage 了！
        // 因為 News Model 掛載了 HasImageFields Trait，
        // 只要執行 delete()，Trait 會自動根據 $imageFields 屬性清理檔案。
        $category->delete();

        $category->writeLog('刪除', $title);

        return redirect()->route('admin.news_category.index')->with('form_success_swal', '消息分類已刪除');
    }

    /**
     * 單獨刪除圖片欄位 (AJAX)
     */
    public function deleteImageField(Request $request, NewsCategory $category)
    {
        // 直接調用優化後的 Trait 方法
        return $category->deleteImageFieldGeneric($request);
    }

    /* -------------------------------------------------------------------------- */
    /* 內部輔助方法 (符合專業開發範式)                                              */
    /* -------------------------------------------------------------------------- */

    /**
     * 渲染表單通用邏輯 (處理分類樹與多語系對照)
     */
    private function renderForm(NewsCategory $category)
    {
        $isEdit = (bool)$category->exists;

        // 從配置讀取層級限制 (預設1層)
        $maxLevel = config('site.category_levels.news', 1);

        // 取得所有根分類並進行遞迴拉平，準備給下拉選單使用
        $rootCategories = NewsCategory::with(['children', 'descs'])
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('display_order', 'asc')
            ->get();

        $parentsList = [];
        // 遍歷根分類，透過遞迴函式去計算每個分類的「縮排」與「是否可當父層」
        foreach ($rootCategories as $root) {
            $this->buildTreeOptions($root, 0, $parentsList, $category->cat_id, $maxLevel);
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
            $category->load('descs');
            foreach ($category->descs as $desc) {
                // 編輯器內容需要解碼回原始 URL，才能正確顯示圖片
                $desc->content = ContentHelper::decodeSiteUrl($desc->content);
                $descMap[$desc->lang_id] = $desc;
            }
        }

        // 預設返回按鈕路由
        $backUrl = $this->getBackUrl('admin.news_category.index');

        return $this->view('admin.news_category.form', compact(
            'category', 'isEdit', 'parentsList', 'langs', 'descMap', 'fileConfigs', 'maxLevel', 'backUrl'
        ));
    }

    /**
     * 遞迴計算分類樹狀結構與縮排
     * @param NewsCategory $category  當前跑到的分類
     * @param int          $level     當前層級深度
     * @param array        &$result   結果儲存陣列
     * @param int|null     $currentId 目前編輯的 ID (用來排除自己不能選自己)
     * @param int          $maxLevel  系統允許的最大深度
     */
    private function buildTreeOptions($category, $level, &$result, $currentId, $maxLevel)
    {
        // 排除自己與自己的後代當作父層，避免無窮迴圈
        if ($category->cat_id == $currentId) return;

        $canBeParent = ($level + 1) < $maxLevel;
        $name = $category->desc->name ?? '未命名';
        $indent = $level > 0 ? str_repeat('　', $level) . '└─ ' : '';

        $result[] = (object)[
            'cat_id'        => $category->cat_id,
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
     * @param int|null $parentId 欲設定的父層 ID
     */
    private function checkLevelLimit($parentId)
    {
        // 如果是設為第一層，就不需要檢查深度
        if (empty($parentId) || $parentId == 0) {
            return null;
        }

        $parent = NewsCategory::find($parentId);
        if (!$parent) return redirect()->back()->with('form_error_swal', '找不到指定的父分類');

        // 往上追溯計算目前深度
        $depth = 1;
        $temp = $parent;
        while ($temp->parent_id > 0) {
            $temp = NewsCategory::find($temp->parent_id);
            if (!$temp) break;
            $depth++;
        }

        $maxLimit = config('site.category_levels.news', 1);

        // 如果「父層深度 + 我自己這一層」超過限制
        if (($depth + 1) > $maxLimit) {
            $this->showMsg(1, "層級過深：此單元最高僅允許 {$maxLimit} 層", [['text' => '返回', 'href' => url()->previous()]], true);
            return redirect()->back();
        }

        // 表示沒問題
        return null;
    }

    /**
     * 通用檔案上傳處理 (邏輯與 NewsController 統一)
     */
    private function handleFileUploads(Request $request, NewsCategory $category)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {
                $category->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $category->$field,
                    $config
                );
            }
        }
    }

    /**
     * 儲存多語系資料
     */
    private function saveTranslations(NewsCategory $category, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            // 如果名稱為空，視為刪除該語系內容
            if (empty($data['name'])) {
                // 刪除前，先抓出舊內容，把裡面的圖片也清掉，避免佔用空間
                $oldDesc = NewsCategoryDesc::where('cat_id', $category->cat_id)->where('lang_id', $langId)->first();
                if ($oldDesc) {
                    SummernoteImageHelper::syncEditorImages(ContentHelper::decodeSiteUrl($oldDesc->content), null);

                    $oldDesc->delete();
                }
                continue;
            }

            NewsCategoryDesc::updateOrInsert(
                ['cat_id' => $category->cat_id, 'lang_id' => $langId],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
                ]
            );
        }
    }
}
