<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    // 列表頁：顯示所有語系
    public function index()
    {
        // 依 display_order 排序顯示
        $langs = Language::orderBy('display_order', 'desc')->get();
        return view('admin.languages.index', compact('langs'));
    }

    // 顯示新增表單
    public function create()
    {
        return view('admin.languages.create');
    }

    // 新增儲存
    public function store(Request $request)
    {
        // 驗證輸入
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'alias' => 'nullable|string|max:50',
            'code' => 'required|string|max:20',      // 例如 zh-TW
            'iso_code' => 'nullable|string|max:10',
            'region' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'display_scope' => 'required|in:both,backend',
        ]);

        // 建立語系
        Language::create([
            'name' => $data['name'],
            'alias' => $data['alias'] ?? null,
            'code' => $data['code'],
            'iso_code' => $data['iso_code'] ?? null,
            'region' => $data['region'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'enabled' => $data['enabled'] ?? 1,
            'display_scope' => $data['display_scope'],
        ]);

        return redirect()->route('admin.languages.index')->with('success', '語系新增完成');
    }

    // 顯示單筆（show）
    public function show(Language $language)
    {
        return view('admin.languages.show', compact('language'));
    }

    // 編輯表單
    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    // 更新
    public function update(Request $request, Language $language)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'alias' => 'nullable|string|max:50',
            'code' => 'required|string|max:20',
            'iso_code' => 'nullable|string|max:10',
            'region' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'display_scope' => 'required|in:both,backend',
        ]);

        $language->update([
            'name' => $data['name'],
            'alias' => $data['alias'] ?? null,
            'code' => $data['code'],
            'iso_code' => $data['iso_code'] ?? null,
            'region' => $data['region'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'enabled' => $data['enabled'] ?? 1,
            'display_scope' => $data['display_scope'],
        ]);

        return redirect()->route('admin.languages.index')->with('success', '語系更新完成');
    }

    // 刪除
    public function destroy(Language $language)
    {
        // 若有外鍵關係（news_desc / category_desc）建議先檢查避免資料遺失
        // 以下簡單示範：若有關聯則阻止刪除（可依需求改成 cascade）
        $hasNewsDesc = DB::table('news_desc')->where('lang_id', $language->lang_id)->exists();
        $hasCatDesc = DB::table('news_category_desc')->where('lang_id', $language->lang_id)->exists();
        if ($hasNewsDesc || $hasCatDesc) {
            return back()->with('error', '此語系已有內容，無法刪除。');
        }

        $language->delete();
        return redirect()->route('admin.languages.index')->with('success', '語系已刪除');
    }
}
