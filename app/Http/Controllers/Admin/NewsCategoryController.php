<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Http\Request;
use App\Models\NewsCategory;
use App\Models\NewsCategoryDesc;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Helpers\ContentHelper;

class NewsCategoryController extends BaseAdminController
{
    protected $pageTitle = '消息分類';

    /**
     * 顯示分類列表頁面，包含多語系表單
     */
    public function index(Request $request)
    {
        // 搜尋條件與每頁顯示數量設定
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        // 取得所有表單，並根據顯示順序排序
        $categories = NewsCategory::with('descs')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('descs', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('display_order', 'desc')
            ->paginate($perPage);

        // 返回分類頁面
        return $this->view('admin.news_category.index', compact('categories', 'search'));
    }

    /**
     * 顯示新增分類表單
     */
    public function create()
    {
        // 取得所有可作為父分類的分類資料與啟用中的語系
        $parents = NewsCategory::all();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        return $this->view('admin.news_category.form', compact('parents', 'langs'));
    }

    /**
     * 儲存表單
     */
    public function store(Request $request)
    {
        // 驗證輸入資料
        $request->validate([
            'parent_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc' => 'required|array',
        ]);

        // 開始資料庫交易，保證資料一致性
        DB::beginTransaction();
        try {
            // 儲存主表資料
            $category = NewsCategory::create([
                'parent_id' => $request->parent_id ?: null,
                'is_visible' => $request->is_visible ?? 1,
                'display_order' => $request->display_order ?? 0,
            ]);

            // 儲存每個語系的描述資料
            foreach ($request->desc as $langId => $desc) {
                if (!empty($desc['name'])) {
                    NewsCategoryDesc::create([
                        'cat_id' => $category->cat_id,
                        'lang_id' => $langId,
                        'name' => $desc['name'],
                        'description' => $desc['description'] ?? null,
                        'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                    ]);
                }
            }

            // 提交交易
            DB::commit();

            // 5️⃣ 回傳訊息
            ContentHelper::showMsg(
                0,
                '消息新增完成',
                [
                    ['text' => '繼續新增', 'href' => route('admin.news_category.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ],
                true
            );

            return redirect()->back();
        } catch (\Throwable $e) {
            // 發生錯誤時回滾交易
            DB::rollBack();
            return back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
        }
    }

    /**
     * 編輯表單
     */
    public function edit(NewsCategory $category)
    {
        // 取得所有可以作為父分類的分類資料，並排除當前編輯的分類，且 parent_id = 當前編輯分類的 cat_id
        $parents = NewsCategory::where('cat_id', '!=', $category->cat_id) // 排除當前分類
            ->where('parent_id', $category->cat_id) // 只選取 parent_id 等於當前分類的項目
            ->get();

        // 取得所有啟用中的語系
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        // 載入該分類的所有描述資料（desc）
        $category->load('descs');
        $isEdit = $category->exists;

        // 轉換 desc 資料成為以 lang_id 為鍵的映射表
        $descMap = [];
        foreach ($category->descs as $desc) {
            // 解碼 content 資料
            $desc->content = ContentHelper::decodeSiteUrl($desc->content);
            $descMap[$desc->lang_id] = $desc;
        }

        // 渲染視圖並傳遞所需的變數
        return $this->view('admin.news_category.form', compact(
            'category',     // 當前編輯的分類資料
            'parents',      // 可選的父類分類資料
            'langs',        // 所有啟用中的語系
            'descMap',      // 每個語系的分類描述
        ));
    }



    /**
     * 更新表單
     */
    public function update(Request $request, NewsCategory $category)
    {
        // 驗證輸入資料
        $request->validate([
            'parent_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc' => 'nullable|array',
        ]);

        // 開始資料庫交易，保證資料一致性
        DB::beginTransaction();
        try {
            // 更新主表資料
            $category->update([
                'parent_id' => $request->parent_id ?: null,
                'is_visible' => $request->is_visible ?? 1,
                'display_order' => $request->display_order ?? 0,
            ]);

            // 更新或插入每個語系的描述資料
            foreach ($request->desc as $langId => $desc) {
                if (empty($desc['name'])) {
                    // 如果名稱為空，刪除該語言描述
                    NewsCategoryDesc::where('cat_id', $category->cat_id)
                        ->where('lang_id', $langId)
                        ->delete();
                    continue;
                }

                // 更新或插入語系描述
                NewsCategoryDesc::updateOrCreate(
                    ['cat_id' => $category->cat_id, 'lang_id' => $langId],
                    [
                        'name' => $desc['name'],
                        'description' => $desc['description'] ?? null,
                        'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                    ]
                );
            }

            // 提交交易
            DB::commit();

            ContentHelper::showMsg(
                0,
                '編輯操作完成',
                [
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ],
                true
            );

            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', '更新失敗: ' . $e->getMessage());
        }
    }
    /**
     * 刪除表單
     */
    public function destroy(NewsCategory $category)
    {
        // 檢查是否有消息使用此分類
        if ($category->news()->exists()) {
            return back()->with('error', '此分類已有消息使用，請先移除關聯後再刪除。');
        }

        // 刪除所有圖片
        // foreach ($this->imageSizes as $inputName => $_) {
        //     if (!empty($news->$inputName)) {
        //         ImageHelper::deleteImage($news->$inputName, 'public');
        //     }
        // }

        // 刪除翻譯
        NewsCategoryDesc::where('cat_id', $category->cat_id)->delete();

        // 刪除分類，並透過外鍵關聯自動刪除相關描述
        $category->delete();

        // return redirect()->route('admin.news_category.index')->with('success', '分類已刪除');

        // 修改：重定向並帶上 'form_success_swal' session 訊息，以便前端 SweetAlert2 捕獲
        return redirect()->route('admin.news_category.index')->with('form_success_swal', '消息已刪除');
    }
}
