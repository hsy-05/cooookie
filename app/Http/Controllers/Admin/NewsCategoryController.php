<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\NewsCategoryRequest; // 引入 Request
use App\Models\{NewsCategory, NewsCategoryDesc};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class NewsCategoryController extends BaseAdminController
{
    // 定義這個 Controller 屬於哪組權限
    protected $permissionName = 'news_category';
    protected $pageTitle = '消息分類管理';

    /**
     * 頁面相關配置
     */
    protected $pageCfg = [
        // 定義哪些欄位需要處理檔案上傳
        'files' => [
            'image_url' => [
                'path'   => 'news_category',     // 儲存路徑
                'width'  => 736,               // 寬度 (若不縮圖可設為 null)
                'height' => 736,               // 高度
                'mode'   => 'scale_fill',     // 處理模式：center_crop, scale_fit
                'bgColor'=> '#D6395C',        // 圖用淡灰底
                'useOriginalName' => false,    // 是否使用原檔名 (false 代表自動生成唯一名稱)
            ],
            // 未來若有 PDF 或 縮圖，直接在這裡增加一組設定即可
        ],
    ];

    /**
     * 顯示分類列表頁面，包含多語系表單
     */
    public function index(Request $request)
    {
        // 取得搜尋關鍵字
        $search = $request->input('search');

        // 建立查詢基礎：預載語言描述與子分類
        $query = NewsCategory::with(['children.descs', 'descs']);

        // 處理搜尋與層級邏輯
        if ($search) {
            // 如果有搜尋，通常會打破樹狀結構，直接列出所有符合的項目
            $categories = $query->whereHas('descs', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->get();
        } else {
            // 如果沒有搜尋，顯示標準樹狀結構：只抓第一層 (parent_id 為 0 或 null)
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
     * 顯示新增分類表單
     */
    public function create()
    {
        return $this->renderForm(new NewsCategory(), false);
    }

    public function store(NewsCategoryRequest $request)
    {
        // 【防呆加強】先進行層級深度判斷，如果不通過，直接回傳錯誤
        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request) {
            try {
                $category = new NewsCategory();

                // 處理圖片 (預留功能)
                $this->handleImageUpload($request, $category);

                // 儲存主表
                $category->fill($request->validated());
                $category->parent_id = $request->parent_id ?: null;
                $category->is_visible = $request->has('is_visible');
                $category->save();

                // 儲存多語系資料
                $this->saveTranslations($category, $request->desc);

                ContentHelper::showMsg(0, '分類新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.news_category.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '新增失敗');
            }
        });
    }

    /**
     * 編輯表單
     */
    public function edit(NewsCategory $category)
    {
        return $this->renderForm($category);
    }

    /**
     * 更新表單
     */
    public function update(NewsCategoryRequest $request, NewsCategory $category)
    {
        // 【防呆加強】編輯時也檢查層級
        $levelError = $this->checkLevelLimit($request->parent_id);
        if ($levelError) return $levelError;

        return DB::transaction(function () use ($request, $category) {
            try {
                // 更新圖片 (預留功能)
                $this->handleImageUpload($request, $category);

                // 更新主表
                $category->update($request->validated());
                $category->parent_id = $request->parent_id ?: null;
                $category->is_visible = $request->has('is_visible');
                $category->save();

                // 更新多語系資料
                $this->saveTranslations($category, $request->desc);

                // 紀錄操作紀錄
                $category->writeLog('編輯', $category->desc->name ?? '未知名分類', [
                    'cat_id' => $category->cat_id,
                    'updated_fields' => array_keys($request->validated()),
                ]);

                ContentHelper::showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('form_error_swal', '更新失敗');
            }
        });
    }

    public function destroy(NewsCategory $category)
    {
        //  檢查：若有子項目則禁止刪除
        if ($category->news()->exists()) {
            return back()->with('form_error_swal', '此分類已有消息使用，無法刪除。');
        }

        // 防呆：如果有子分類，也要禁止刪除
        if ($category->children()->exists()) {
            return back()->with('form_error_swal', '請先刪除底下的子分類。');
        }

        // 先抓取標題供 Log 使用
        $category->load('desc');
        $title = $category->desc->title ?? '未知名分類';

        // 刪除相關聯的所有實體檔案 (防呆：避免伺服器留下一堆廢圖)
        foreach (array_keys($this->pageCfg['files']) as $field) {
            if ($category->$field) {
                ImageHelper::deleteImage($category->$field, 'public');
            }
        }

        // 刪除資料庫紀錄
        $category->descs()->delete();
        $category->delete();

        // 紀錄操作紀錄
        $category->writeLog('刪除', $title);

        return redirect()->route('admin.news_category.index')->with('form_success_swal', '消息分類已刪除');
    }

    /* --- 內部輔助方法 (符合 NewsController 邏輯) --- */

    /**
     * 專業防呆：獨立出的層級深度檢查邏輯
     * 這裡完整保留了你原本的 ContentHelper 與 while 迴圈判斷
     */
    private function checkLevelLimit($parentId)
    {
        // 如果是設為第一層，就不需要檢查深度
        if (empty($parentId) || $parentId == 0) {
            return null;
        }

        $parent = NewsCategory::find($parentId);
        $backUrl = url()->previous();

        if (!$parent) {
            ContentHelper::showMsg(1, '找不到指定的父分類', [['text' => '返回表單', 'href' => $backUrl]], true);
            return redirect()->back();
        }

        // 使用你原本的 while 迴圈，逐層往上找，計算目前的深度
        $parentLevel = 1;
        $tempParent = $parent;
        while ($tempParent->parent_id > 0) {
            $tempParent = NewsCategory::find($tempParent->parent_id);
            if (!$tempParent) break;
            $parentLevel++;
        }

        // 從設定檔讀取上限 (防呆預設為 2)
        $maxLimit = config('site_settings.category_levels.news', 2);

        // 如果「父層深度 + 我自己這一層」超過限制
        if (($parentLevel + 1) > $maxLimit) {
            ContentHelper::showMsg(1, "違反層級限制：消息分類最高僅允許 {$maxLimit} 層", [['text' => '返回表單', 'href' => $backUrl]], true);
            return redirect()->back();
        }

        return null; // 代表沒問題
    }

    /**
     * 處理表單顯示邏輯：準備新增或編輯所需的資料
     */
    private function renderForm(NewsCategory $category)
    {
        // 判斷當前是「新增」還是「編輯」
        $isEdit = (bool)$category->exists;

        // 【專業點】從全域設定讀取此單元的層級限制。若沒設定，預設為 2 層 (大類 > 小類)
        $maxLevel = config('site_settings.category_levels.news', 2);

        // 抓取所有的根分類 (最頂層)，並預載子分類及多語系資料，減少 SQL 查詢次數 (Eager Loading)
        $rootCategories = NewsCategory::with(['children', 'descs'])
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('display_order', 'asc')
            ->get();

        // 用來存放「拉平」後的下拉選單選項
        $parentsList = [];

        // 遍歷根分類，透過遞迴函式去計算每個分類的「縮排」與「是否可當父層」
        foreach ($rootCategories as $root) {
            $this->buildTreeOptions($root, 0, $parentsList, $category->cat_id, $maxLevel);
        }

        // 獲取目前啟用的語系設定
        $langs = $this->getActiveLanguages();

        // 將配置傳給前端，以便顯示建議尺寸提示
        $fileConfigs = $this->pageCfg['files'];

        // 建立語系資料對照表，方便 View 使用 $descMap[語系ID] 直接抓到內容
        $descMap = [];
        if ($isEdit) {
            $category->load('descs');
            foreach ($category->descs as $desc) {
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.news_category.form', compact('category', 'isEdit', 'parentsList', 'langs', 'descMap', 'fileConfigs', 'maxLevel'));
    }

    /**
     * 遞迴計算分類樹狀結構
     * @param NewsCategory $category 當前跑到的分類物件
     * @param int $level             當前的深度層級 (0 是最頂層)
     * @param array &$result         引用傳遞，將處理好的資料塞進此結果陣列
     * @param int $currentId         目前正在編輯的 ID，用來排除「自己不能當自己的父層」
     * @param int $maxLevel          此單元允許的最大總層級
     */
    private function buildTreeOptions($category, $level, &$result, $currentId, $maxLevel)
    {
        // 【防呆】編輯時，不能選擇自己或自己的子孫作為父層，否則會發生邏輯死循環
        if ($category->cat_id == $currentId) {
            return;
        }

        // 【層級邏輯】判斷該分類是否還有餘額可以接收「子分類」
        // 原理：如果我是 level 0，我的下一層是 1。如果 maxLevel 是 1，那 (0+1 < 1) 為 false，我就不能當父層。
        $canBeParent = ($level + 1) < $maxLevel;

        // 取得名稱，優先使用關聯資料中的名稱
        $name = $category->desc->name ?? ($category->descs->first()->name ?? '未命名');

        // 生成縮排符號，層級越高縮越進去
        $indent = $level > 0 ? str_repeat('　', $level) . '└─ ' : '';

        // 將此分類包裝成物件，存入結果
        $result[] = (object)[
            'cat_id'        => $category->cat_id,
            'name'          => $indent . $name,
            'can_be_parent' => $canBeParent // 給 Blade 判斷是否要 disabled
        ];

        // 如果還有子分類，繼續往深處跑 (遞迴)
        if ($category->children && $category->children->count() > 0) {
            foreach ($category->children as $child) {
                $this->buildTreeOptions($child, $level + 1, $result, $currentId, $maxLevel);
            }
        }
    }

    /**
     * 基本格式驗證及層級防呆檢查
     * 注意：現在會回傳 Redirect 物件或 null，呼叫處必須 return 它
     */
    private function validateRequest(Request $request)
    {
        // 1. 基本格式驗證
        $request->validate([
            // 修改點：加上 'sometimes' 或手動判斷，避開 parent_id = 0 的檢查
            'parent_id'     => 'nullable|integer',
            'is_visible'    => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image_url'         => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc'          => 'nullable|array',
            'desc.*.name'   => 'required_with:desc.*|string|max:255',
        ]);

        // 如果 parent_id > 0，才進入深度檢查與存在檢查
        if ($request->filled('parent_id') && $request->parent_id > 0) {
            $parent = NewsCategory::find($request->parent_id);

            // 準備返回的連結：如果是編輯就回編輯頁，新增就回新增頁
            $backUrl = url()->previous();

            if (!$parent) {
                // 明確指定回傳連結，不要讓它用 javascript:history.go(-1)
                ContentHelper::showMsg(1, '找不到指定的父分類', [['text' => '返回表單', 'href' => $backUrl]], true);
                return redirect()->back();
            }

            // B. 計算該父層真正的深度 (支援 1~N 層)
            $parentLevel = 1;
            $tempParent = $parent;
            while ($tempParent->parent_id > 0) {
                $tempParent = NewsCategory::find($tempParent->parent_id);
                // 防呆：避免資料庫關聯出錯導致死循環
                if (!$tempParent) break;
                $parentLevel++;
            }

            // C. 取得上限設定
            $maxLimit = config('site_settings.category_levels.news', 1);

            // D. 判斷是否超過上限
            if (($parentLevel + 1) > $maxLimit) {
                // 同樣明確指定連結
                ContentHelper::showMsg(1, "違反層級限制：消息分類最高僅允許 {$maxLimit} 層", [['text' => '返回表單', 'href' => $backUrl]], true);
                return redirect()->back();
            }
        }
        return null;
    }

    private function handleImageUpload(Request $request, NewsCategory $category)
    {
        foreach ($this->pageCfg['files'] as $field => $config) {
            // 如果 Request 裡有這個檔案，才進行處理
            if ($request->hasFile($field)) {
                $category->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $category->$field, // 傳入舊路徑以供刪除
                    $config
                );
            }
        }
    }

    private function saveTranslations(NewsCategory $category, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            // 如果名稱為空，視為刪除該語系內容
            if (empty($data['name'])) {
                NewsCategoryDesc::where('cat_id', $category->cat_id)->where('lang_id', $langId)->delete();
                continue;
            }

            DB::table('news_category_desc')->updateOrInsert(
                ['cat_id' => $category->cat_id, 'lang_id' => $langId],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'content'     => ContentHelper::encodeSiteUrl($data['content'] ?? ''),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    /**
    * 刪除圖片欄位的通用方法
    * 前端會傳入要刪除的欄位名稱 (例如 image_url)，這樣這個方法就可以通用於多個圖片欄位
    */
    public function deleteImageField(Request $request, NewsCategory $category)
    {
        // 調用 Trait 裡面的通用邏輯，傳入當前的 $category 模型實例
        // 並明確告訴 Trait 要刪除的欄位名稱 (從前端傳來，或直接寫死在控制器)
        return $this->deleteImageFieldGeneric($request, $category);
    }


}
