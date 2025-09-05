<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Language;

class NewsController extends Controller
{
    // 列表頁：顯示已啟用的消息（以 display_order, created_at 排序）
    public function index(Request $request)
    {
        // 可從 query 取得語系，例如 ?lang=zh-TW 或 ?lang=1，這裡簡化：用第一個啟用語系或預設
        $langId = $request->input('lang_id') ?: (Language::where('enabled',1)->value('lang_id') ?: 1);

        // 取主表並帶入翻譯（descs），在 view 選擇正確語系文字
        $newsList = News::with('descs', 'category')
                        ->where('is_visible', 1)
                        ->orderByDesc('display_order')
                        ->orderByDesc('created_at')
                        ->paginate(8);

        return view('frontend.news.index', compact('newsList','langId'));
    }

    // 單一新聞內頁
    public function show(News $news, Request $request)
    {
        $langId = $request->input('lang_id') ?: (Language::where('enabled',1)->value('lang_id') ?: 1);
        $news->load('descs', 'category');

        // 找到對應語系的 desc，若無則 fallback 第一筆
        $desc = $news->descs->firstWhere('lang_id', $langId) ?: $news->descs->first();

        return view('frontend.news.show', compact('news','desc'));
    }
}
