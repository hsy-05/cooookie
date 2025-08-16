<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsCategory;
use App\Models\NewsCategoryDesc;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class NewsCategoryController extends Controller
{
    /**
     * 列表：顯示所有分類（含各語系名稱）
     */
    public function index()
    {
        // 取出分類與其 translations
        $categories = NewsCategory::with('descs')->orderBy('display_order', 'desc')->get();
        return view('admin.news_category.index', compact('categories'));
    }

    /**
     * 顯示建立表單
     */
    public function create()
    {
        // 取得可當父類的分類與啟用的語系
        $parents = NewsCategory::all();
        $langs = Language::where('enabled', 1)->orderBy('sort_order', 'desc')->get();
        return view('admin.news_category.create', compact('parents', 'langs'));
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
            foreach ($request->input('desc') as $langId => $d) {
                // 若 name 欄位為空，跳過
                if (empty($d['name'])) continue;

                NewsCategoryDesc::insert([
                    'cat_id' => $category->cat_id,
                    'lang_id' => (int)$langId,
                    'name' => $d['name'],
                    'description' => $d['description'] ?? null,
                    'content' => $d['content'] ?? null,
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
        $langs = Language::where('enabled', 1)->orderBy('sort_order', 'desc')->get();
        $news_category->load('descs');

        // 轉成以 lang_id 為 key 的陣列便於 Blade 填值
        $descMap = [];
        foreach ($news_category->descs as $d) {
            $descMap[$d->lang_id] = $d;
        }

        return view('admin.news_category.edit', compact('news_category', 'parents', 'langs', 'descMap'));
    }

    /**
     * 更新：包含 upsert 多語系 desc（使用 updateOrInsert，因 desc 表沒有 id）
     */
    public function update(Request $request, NewsCategory $news_category)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // 更新主表
            $news_category->update([
                'parent_id' => $request->parent_id ?: null,
                'parent_ids' => $news_category->parent_ids, // 若要 rebuild 可自行實作
                'super_id' => $news_category->super_id,
                'is_visible' => $request->is_visible ?? 1,
                'display_order' => $request->display_order ?? 0,
            ]);

            // upsert 每個語系
            foreach ($request->input('desc') as $langId => $d) {
                // if name empty -> delete existing translation (可選)
                if (empty($d['name'])) {
                    DB::table('news_category_desc')->where('cat_id', $news_category->cat_id)->where('lang_id', $langId)->delete();
                    continue;
                }

                // updateOrInsert: 若存在 (cat_id, lang_id) 則更新，否則插入
                DB::table('news_category_desc')->updateOrInsert(
                    ['cat_id' => $news_category->cat_id, 'lang_id' => (int)$langId],
                    [
                        'name' => $d['name'],
                        'description' => $d['description'] ?? null,
                        'content' => $d['content'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(), // 若 updating 會忽略 created_at
                    ]
                );
            }

            DB::commit();
            return redirect()->route('admin.news_category.index')->with('success', '分類更新成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', '更新失敗：' . $e->getMessage());
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
