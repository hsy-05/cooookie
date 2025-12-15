<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsCategory;
use App\Models\NewsCategoryDesc;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Helpers\ContentHelper;

class NewsCategoryController extends Controller
{
    /**
     * 列表：顯示所有分類（含各語系名稱）
     */
    public function index()
    {
        // 取出分類與其 translations
        $category = NewsCategory::with('descs')->orderBy('display_order', 'desc')->get();
        return view('admin.news_category.index', compact('category'));
    }

    /**
     * 顯示建立表單
     */
    public function create()
    {
        // 取得可當父類的分類與啟用的語系
        $parents = NewsCategory::all();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        return view('admin.news_category.form', compact('parents', 'langs'));
    }

    /**
     * 儲存：建立 news_category 主表 + 多語系描述至 news_category_desc
     */
    public function store(Request $request)
    {
        // 驗證主表欄位（語系內容另行處理）
        $request->validate([
            'parent_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc' => 'required|array', // 必須至少有一個語系輸入
        ]);

        // 使用 transaction 確保主表與描述一致
        DB::beginTransaction();
        try {
            $category = NewsCategory::create([
                'parent_id' => $request->parent_id ?: null,
                'parent_ids' => null, // 你可以在這裡實作 parent_ids 的建立邏輯
                'super_id' => null,
                'is_visible' => $request->is_visible ?? 1,
                'display_order' => $request->display_order ?? 0,
            ]);

            // desc 是前端傳過來的陣列 desc[lang_id][name,description,content]
            foreach ($request->input('desc') as $langId => $desc) {
                // 若 name 欄位為空，跳過
                if (empty($desc['name'])) continue;

                NewsCategoryDesc::insert([
                    'cat_id' => $category->cat_id,
                    'lang_id' => (int)$langId,
                    'name' => $desc['name'],
                    'description' => $desc['description'] ?? null,
                    'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.news_category.index')->with('success', '分類新增成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
        }
    }

    /**
     * 顯示單筆詳細（含所有語系）
     */
    public function show(NewsCategory $news_category)
    {
        $news_category->load('descs');
        return view('admin.news_category.show', ['category' => $news_category]);
    }

    /**
     * 編輯表單（填入每個語系的值）
     */
    public function edit(NewsCategory $news_category)
    {
        $parents = NewsCategory::where('cat_id', '!=', $news_category->cat_id)->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $news_category->load('descs');
        $isEdit = $news_category->exists;

        // 轉成以 lang_id 為 key 的陣列便於 Blade 填值
        $descMap = [];
        foreach ($news_category->descs as $desc) {
            $descMap[$desc->lang_id] = $desc;
        }

        return view('admin.news_category.form', compact('news_category', 'isEdit', 'parents', 'langs', 'descMap'));
    }

    /**
     * 更新
     */
    public function update(Request $request, NewsCategory $news_category)
    {
        // 1️⃣ 驗證輸入
        $request->validate([
            'parent_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc' => 'required|array',
        ]);

        // 2️⃣ 開啟資料庫交易
        DB::beginTransaction();

        try {
            // 3️⃣ 更新主表
            $news_category->update([
                'parent_id' => $request->parent_id ?: null,
                'parent_ids' => $news_category->parent_ids, // 若要 rebuild 可自行實作
                'super_id' => $news_category->super_id,
                'is_visible' => $request->is_visible ?? 1,
                'display_order' => $request->display_order ?? 0,
            ]);

            // 4️⃣ 更新或插入每個語系的描述
            foreach ($request->input('desc') as $langId => $desc) {

                // 若名稱為空，刪除現有翻譯
                if (empty($desc['name'])) {
                    DB::table('news_category_desc')
                        ->where('cat_id', $news_category->cat_id)
                        ->where('lang_id', $langId)
                        ->delete();
                    continue;
                }

                // updateOrInsert: 若存在則更新，否則插入
                DB::table('news_category_desc')->updateOrInsert(
                    [
                        'cat_id' => $news_category->cat_id,
                        'lang_id' => (int)$langId
                    ],
                    [
                        'name' => $desc['name'],
                        'description' => $desc['description'] ?? null,
                        'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? ''),
                        'updated_at' => now(),
                        'created_at' => now(), // 若更新則忽略 created_at
                    ]
                );
            }

            // 5️⃣ 提交交易，變更正式寫入資料庫
            DB::commit();

            // 6️⃣ 顯示成功訊息
            ContentHelper::showMsg(
                0, // 0 = 成功訊息
                '編輯操作完成',
                [
                    ['text' => '繼續編輯', 'href' => route('admin.news_category.edit', $news_category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ],
                true // 自動跳轉
            );

            return redirect()->back();
        } catch (\Throwable $e) {
            // 7️⃣ 發生錯誤 → 回滾交易，撤銷所有變更
            DB::rollBack();

            // 8️⃣ 顯示錯誤訊息
            ContentHelper::showMsg(
                1, // 1 = 錯誤訊息
                '更新失敗：' . $e->getMessage(),
                [
                    ['text' => '返回編輯', 'href' => route('admin.news_category.edit', $news_category->cat_id)],
                    ['text' => '返回列表', 'href' => route('admin.news_category.index')],
                ],
                false // 不自動跳轉，讓使用者選擇
            );

            return redirect()->back()->withInput();
        }
    }


    /**
     * 刪除：同時刪除 news_category_desc（因外鍵 cascade 已處理）
     */
    public function destroy(NewsCategory $news_category)
    {
        // 若要保護資料關聯（例如有 news 指向該分類），請先檢查
        $hasNews = DB::table('news')->where('cat_id', $news_category->cat_id)->exists();
        if ($hasNews) {
            return back()->with('error', '此分類已有消息使用，請先移除關聯後再刪除。');
        }

        $news_category->delete(); // 因為 desc 表外鍵設 cascade，會自動刪除 desc
        return redirect()->route('admin.news_category.index')->with('success', '分類已刪除');
    }
}
