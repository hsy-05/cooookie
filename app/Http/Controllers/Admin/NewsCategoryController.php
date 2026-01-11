<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Models\{NewsCategory, NewsCategoryDesc, Language};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class NewsCategoryController extends BaseAdminController
{
    protected $pageTitle = '消息分類';

    /**
     * 圖片欄位尺寸設定
     * key = input name
     * value = [width, height]
     */
    protected $imageSizes = [
        'image' => [600, 400],
    ];

    /**
     * 顯示分類列表頁面，包含多語系表單
     */
    public function index(Request $request)
    {
        // 1. 取得搜尋關鍵字
        $search = $request->input('search');

        // 2. 建立查詢基礎：預載語言描述與子分類
        $query = NewsCategory::with(['children.descs', 'descs']);

        // 3. 處理搜尋與層級邏輯
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

    public function store(Request $request)
    {
        $validRes = $this->validateRequest($request);
        // 如果有回傳 Redirect 物件，代表防呆觸發了，必須立刻 return 回去給瀏覽器
        if ($validRes) {
            return $validRes;
        }
        return DB::transaction(function () use ($request) {
            try {
                $category = new NewsCategory();

                // 1. 處理圖片 (預留功能)
                $this->handleImageUpload($request, $category);

                // 2. 儲存主表
                $category->fill([
                    'parent_id'     => $request->parent_id ?: null,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ])->save();

                // 3. 儲存多語系資料
                $this->saveTranslations($category, $request->desc);

                ContentHelper::showMsg(0, '分類新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.news_category.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗');
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
    public function update(Request $request, NewsCategory $category)
    {
        $validRes = $this->validateRequest($request);
        // 如果有回傳 Redirect 物件，代表防呆觸發了，必須立刻 return 回去給瀏覽器
        if ($validRes) {
            return $validRes;
        }
        return DB::transaction(function () use ($request, $category) {
            try {
                // 1. 更新圖片 (預留功能)
                $this->handleImageUpload($request, $category);

                // 2. 更新主表
                $category->update([
                    'parent_id'     => $request->parent_id ?: null,
                    'is_visible' => $request->input('is_visible') === '1',
                    'display_order' => $request->display_order ?? 0,
                ]);

                // 3. 更新多語系資料
                $this->saveTranslations($category, $request->desc);

                ContentHelper::showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Category Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    public function destroy(NewsCategory $category)
    {
        // 業務邏輯檢查：若有子項目則禁止刪除
        if ($category->news()->exists()) {
            return back()->with('error', '此分類已有消息使用，無法刪除。');
        }

        DB::transaction(function () use ($category) {
            // 刪除圖片檔案
            foreach (array_keys($this->imageSizes) as $field) {
                if ($category->$field) ImageHelper::deleteImage($category->$field, 'public');
            }
            $category->descs()->delete();
            $category->delete();
        });

        return redirect()->route('admin.news_category.index')->with('form_success_swal', '分類已刪除');
    }

    /* --- 內部輔助方法 (符合 NewsController 邏輯) --- */

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

        // 建立語系資料對照表，方便 View 使用 $descMap[語系ID] 直接抓到內容
        $descMap = [];
        if ($isEdit) {
            $category->load('descs');
            foreach ($category->descs as $desc) {
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.news_category.form', [
            'category'   => $category,
            'isEdit'     => $isEdit,
            'parents'    => $parentsList, // 這是處理好的「層級選單陣列」
            'langs'      => $langs,
            'descMap'    => $descMap,
            'imageSizes' => $this->imageSizes // 傳遞圖片尺寸規範給前端參考
        ]);
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
            'image'         => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
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
        foreach ($this->imageSizes as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                if ($category->$field) ImageHelper::deleteImage($category->$field, 'public');

                $file = $request->file($field);
                $processed = ImageHelper::processImage($file, $width, $height, 'center_crop');
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = "news_category/{$filename}"; // 存放在不同資料夾

                ImageHelper::saveProcessedImage($processed, $fullPath, 'public', 90, 'jpeg');
                $category->$field = $fullPath;
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

    private function getActiveLanguages()
    {
        return Language::where('enabled', 1)->orderByDesc('display_order')->get();
    }
}
