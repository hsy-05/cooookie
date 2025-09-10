<?php

namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\News;
    use App\Models\NewsDesc;
    use App\Models\NewsCategory;
    use App\Models\Language;
    use Intervention\Image\ImageManager;
    use Intervention\Image\Drivers\Gd\Driver;
    use App\Helpers\ContentHelper;
    use App\Http\Controllers\Admin\BaseAdminController;

    class NewsController extends BaseAdminController
    {
        protected $pageTitle = '最新消息';

    // 列表：載入 news 主表與所有 desc（可在 view 選語系顯示）
    public function index(Request $request)
    {
        // 加入每頁筆數參數，預設 8
        $perPage = $request->input('per_page', 8);
        // 資料查詢與關聯載入
        $newsList = News::with(['descs', 'category'])
            ->orderBy('display_order', 'desc')
            ->orderBy('news_id', 'desc')
            ->paginate($perPage); // 套用每頁筆數

        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        return $this->view('admin.news.index', compact('newsList', 'langs'));
    }

    // 新增表單：需要分類與語系清單
    public function create()
    {
        $cats = NewsCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        return $this->view('admin.news.form', compact('cats', 'langs'));
    }

    // 儲存
    public function store(Request $request)
    {
        // 驗證主表欄位（desc 內容會以陣列方式在下方處理）
        $request->validate([
            'cat_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096', // 4MB
        ]);

        // 圖片處理：中心裁切 600x400（coverDown 不會放大原圖）
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file)->coverDown(600, 400, 'center');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $saveDir = storage_path('app/public/news');
            if (!file_exists($saveDir)) mkdir($saveDir, 0755, true);
            $img->save($saveDir . '/' . $filename);
            $imagePath = 'news/' . $filename;
        }

        // 建立 news 主表
        $news = News::create([
            'cat_id' => $request->cat_id,
            'is_visible' => $request->is_visible ?? true,
            'display_order' => $request->display_order ?? 0,
            'image' => $imagePath,
        ]);

        // 建立 desc：前端應傳 desc[lang_id][title|content]
        if ($request->has('desc') && is_array($request->desc)) {
            foreach ($request->desc as $lang_id => $desc) {
                // 若 title 為空則略過；若要強制每個語系必填可在驗證時加入 rules
                if (!empty($desc['title'])) {
                    NewsDesc::create([
                        'news_id' => $news->news_id,
                        'lang_id' => $lang_id,
                        'title' => $desc['title'],
                        'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? null),
                    ]);
                }
            }
        }

        ContentHelper::showMsg(
            0,
            '消息新增完成',
            [
                ['text' => '繼續新增', 'href' => route('admin.news.create')],
                ['text' => '返回列表', 'href' => route('admin.news.index')],
            ],
            true
        );
        return redirect()->back();
    }

    // 顯示單筆（含所有語系翻譯）
    /* public function show(News $news)
    {
        $news->load('descs', 'category.descs');
        return $this->view('admin.news.show', compact('news'));
    } */

    // 編輯表單
    public function edit(News $news)
    {
        $cats = NewsCategory::with('descs')->where('is_visible', 1)->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $news->load('descs');
        $isEdit = $news->exists;

        // 轉成以 lang_id 為 key 的陣列，便於 blade 填值
        $descMap = [];
        foreach ($news->descs as $desc) {
            $desc->content = ContentHelper::decodeSiteUrl($desc->content);
            $descMap[$desc->lang_id] = $desc;
        }
        return $this->view('admin.news.form', compact('news', 'isEdit', 'cats', 'langs', 'descMap'));
    }

    // 更新
    public function update(Request $request, News $news)
    {
        $request->validate([
            'cat_id' => 'nullable|exists:news_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        // 圖片處理（若有新圖，存新圖並刪舊圖）
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $manager = new ImageManager(new Driver());
            $img = $manager->read($file)->coverDown(600, 400, 'center');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $saveDir = storage_path('app/public/news');
            if (!file_exists($saveDir)) mkdir($saveDir, 0755, true);
            $img->save($saveDir . '/' . $filename);

            // 刪除舊圖
            if ($news->image && file_exists(storage_path('app/public/' . $news->image))) {
                @unlink(storage_path('app/public/' . $news->image));
            }

            $news->image = 'news/' . $filename;
        }

        // 更新主表
        $news->cat_id = $request->cat_id;
        $news->is_visible = $request->is_visible ?? true;
        $news->display_order = $request->display_order ?? 0;
        $news->save();

        // 更新 desc：若存在則 update，否則 create；前端傳 desc[lang_id]
        if ($request->has('desc') && is_array($request->desc)) {
            foreach ($request->desc as $lang_id => $desc) {
                $existing = NewsDesc::where('news_id', $news->news_id)
                    ->where('lang_id', $lang_id)
                    ->first();
                if ($existing) {
                    $existing->update([
                        'title' => $desc['title'] ?? '',
                        'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? null),
                    ]);
                } else {
                    if (!empty($desc['title'])) {
                        NewsDesc::create([
                            'news_id' => $news->news_id,
                            'lang_id' => $lang_id,
                            'title' => $desc['title'],
                            'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? null),
                        ]);
                    }
                }
            }
        }

        // 儲存成功後
        ContentHelper::showMsg(
            0,
            '編輯操作完成',
            [
                ['text' => '繼續編輯', 'href' => route('admin.news.edit', $news->news_id)],
                ['text' => '返回列表', 'href' => route('admin.news.index')],
            ],
            true // 是否自動跳轉
        );


        return redirect()->back();
    }

    // 刪除
    public function destroy(News $news)
    {
        // 刪除翻譯
        NewsDesc::where('news_id', $news->news_id)->delete();

        // 刪除圖片
        if ($news->image && file_exists(storage_path('app/public/' . $news->image))) {
            @unlink(storage_path('app/public/' . $news->image));
        }

        $news->delete();
        return redirect()->route('admin.news.index')->with('success', '消息已刪除');
    }
}
