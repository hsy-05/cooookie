<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\ProductCategory;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use App\Helpers\ContentHelper;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseAdminController
{
    protected $pageTitle = '最新消息';

    // 列表：載入 product 主表與所有 desc（可在 view 選語系顯示）
    public function index(Request $request)
    {
        // 加入每頁筆數參數，預設 8
        $perPage = $request->input('per_page', 8);
        // 獲取搜尋關鍵字
        $search = $request->input('search');

        // 資料查詢與關聯載入
        $productList = Product::with(['descs', 'category'])
            ->orderBy('display_order', 'desc')
            ->orderBy('product_id', 'desc');

        // 如果有搜尋關鍵字，則加入篩選條件
        if ($search) {
            $productList->whereHas('descs', function ($query) use ($search) {
                // 搜尋 ProductDesc 表中的 title 欄位
                $query->where('title', 'like', '%' . $search . '%');
            });
        }

        // 套用每頁筆數和分頁
        $productList = $productList->paginate($perPage);

        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        // 將搜尋關鍵字也傳遞給視圖，以便在搜尋框中保留
        return $this->view('admin.product.index', compact('productList', 'langs', 'search'));
    }

    // 新增表單：需要分類與語系清單
    public function create()
    {
        $cats = ProductCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        return $this->view('admin.product.form', compact('cats', 'langs'));
    }

    // 儲存
    public function store(Request $request)
    {
        // 驗證主表欄位（desc 內容會以陣列方式在下方處理）
        $request->validate([
            'cat_id' => 'nullable|exists:product_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096', // 4MB
        ]);

        // 圖片處理：中心裁切 600x400（coverDown 不會放大原圖）
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $saveDir = 'product'; // 儲存子目錄

            try {
                // 1. 處理圖片 (裁切/縮圖)
                $processedImage = ImageHelper::processImage($file, 600, 400, 'center_crop');

                // 2. 生成唯一檔名
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename;

                // 3. 儲存處理後的圖片
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                $imagePath = $fullPath;
            } catch (\Exception $e) {
                // 圖片處理或儲存失敗，可以記錄日誌或返回錯誤訊息
                return redirect()->back()->withInput()->with('error', '圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        // 建立 product 主表
        $product = Product::create([
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
                    ProductDesc::create([
                        'product_id' => $product->product_id,
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
                ['text' => '繼續新增', 'href' => route('admin.product.create')],
                ['text' => '返回列表', 'href' => route('admin.product.index')],
            ],
            true
        );
        return redirect()->back();
    }

    // 編輯表單
    public function edit(Product $product)
    {
        $cats = ProductCategory::with('descs')->where('is_visible', 1)->orderBy('display_order', 'desc')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $product->load('descs');
        $isEdit = $product->exists;

        // 轉成以 lang_id 為 key 的陣列，便於 blade 填值
        $descMap = [];
        foreach ($product->descs as $desc) {
            $desc->content = ContentHelper::decodeSiteUrl($desc->content);
            $descMap[$desc->lang_id] = $desc;
        }
        return $this->view('admin.product.form', compact('product', 'isEdit', 'cats', 'langs', 'descMap'));
    }

    // 更新
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'cat_id' => 'nullable|exists:product_category,cat_id',
            'is_visible' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'desc' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // 圖片處理
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $saveDir = 'product';

                try {
                    ImageHelper::deleteImage($product->image, 'public');
                    $processedImage = ImageHelper::processImage($file, 600, 400, 'center_crop');
                    $filename = ImageHelper::generateUniqueFilename($file);
                    $fullPath = $saveDir . '/' . $filename;
                    ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, 'jpeg');
                    $product->image = $fullPath;
                } catch (\Throwable $e) {
                    return redirect()->back()->withInput()->with('error', '圖片處理失敗: ' . $e->getMessage());
                }
            }

            // 更新主表
            $product->update([
                'cat_id' => $request->cat_id,
                'is_visible' => $request->is_visible ?? true,
                'display_order' => $request->display_order ?? 0,
            ]);

            // 更新 desc
            if ($request->filled('desc')) {
                foreach ($request->desc as $lang_id => $desc) {
                    if (empty($desc['title'])) {
                        DB::table('product_desc')->where('product_id', $product->product_id)->where('lang_id', $lang_id)->delete();
                        continue;
                    }

                    DB::table('product_desc')->updateOrInsert(
                        ['product_id' => $product->product_id, 'lang_id' => $lang_id],
                        [
                            'title' => $desc['title'],
                            'content' => ContentHelper::encodeSiteUrl($desc['content'] ?? null),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            DB::commit();

            ContentHelper::showMsg(
                0,
                '編輯操作完成',
                [
                    ['text' => '繼續編輯', 'href' => route('admin.product.edit', $product->product_id)],
                    ['text' => '返回列表', 'href' => route('admin.product.index')],
                ],
                true
            );

            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            ContentHelper::showMsg(
                1, // 1 = 錯誤訊息
                '更新失敗：' . $e->getMessage(),
                [
                    ['text' => '返回編輯', 'href' => route('admin.product.edit', $product->product_id)],
                    ['text' => '返回列表', 'href' => route('admin.product.index')],
                ],
                false // 失敗不自動跳轉，讓使用者選擇
            );

            return redirect()->back()->withInput();
        }
    }


    // 刪除
    public function destroy(Product $product)
    {
        // 刪除翻譯
        ProductDesc::where('product_id', $product->product_id)->delete();

        // // 只有有圖片才刪除圖片
        if (!empty($product->image)) {
            ImageHelper::deleteImage($product->image, 'public');
        }

        $product->delete();

        // 修改：重定向並帶上 'form_success_swal' session 訊息，以便前端 SweetAlert2 捕獲
        return redirect()->route('admin.product.index')->with('form_success_swal', '消息已刪除');
    }
}
