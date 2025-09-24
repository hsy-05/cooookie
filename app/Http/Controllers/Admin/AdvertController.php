<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advert;
use App\Models\AdvertDesc;
use App\Models\AdvertCategory;
use App\Models\Language;
// use Intervention\Image\ImageManager; // 不再直接使用 Intervention Image Manager
// use Intervention\Image\Drivers\Gd\Driver; // 不再直接使用 Intervention Image Driver
use App\Helpers\ContentHelper;
use App\Helpers\ImageHelper; // 引入 ImageHelper 處理圖片相關操作
use App\Http\Controllers\Admin\BaseAdminController;
use Illuminate\Support\Facades\Storage; // 引入 Storage Facade 用於刪除操作 (雖然 ImageHelper 已封裝，但保留以防萬一或未來擴展)

class AdvertController extends BaseAdminController
{
    protected $pageTitle = '廣告';

    // 列表
    public function index(Request $request)
    {
        // 加入每頁筆數參數，預設 8
        $perPage = $request->input('per_page', 8);
        // 資料查詢與關聯載入
        $adverts = Advert::with(['descs', 'category'])
            ->orderBy('display_order', 'desc')
            ->orderBy('adv_id', 'desc')
            ->paginate($perPage); // 套用每頁筆數

        return $this->view('admin.advert.index', compact('adverts'));
    }

    // 顯示新增表單
    public function create()
    {
        // 載入分類，包含 cat_func_scope / cat_params (model 已 cast 成 array)
        $categories = AdvertCategory::orderBy('display_order')->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();

        // 傳給 view：categories 是 collection，可直接 json_encode 使用
        return $this->view('admin.advert.form', [
            'advert' => new Advert(),
            'cats' => $categories,
            'langs' => $langs,
        ]);
    }

    // 儲存
    public function store(Request $request)
    {
        $request->validate([
            'cat_id' => 'required|exists:advert_category,cat_id',
        ]);

        $category = AdvertCategory::findOrFail($request->cat_id);
        $scope = (array) ($category->cat_func_scope ?? []);
        $catParams = (array) ($category->cat_params ?? []);
        $fieldParams = $catParams['fields'] ?? [];

        $rules = ['cat_id' => 'required|exists:advert_category,cat_id'];
        // 動態加入圖片欄位驗證
        if (in_array('adv_img_url', $scope)) {
            $rules['adv_img_url'] = 'nullable|image|mimes:jpg,jpeg,png|max:4096';
        }
        if (in_array('adv_img_m_url', $scope)) {
            $rules['adv_img_m_url'] = 'nullable|image|mimes:jpg,jpeg,png|max:4096';
        }
        // 其他欄位視 scope 決定
        if (in_array('adv_link_url', $scope)) {
            $rules['adv_link_url'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules);

        $save = [
            'cat_id' => $validated['cat_id'],
            'display_order' => $request->display_order ?? 0,
            'is_visible' => $request->is_visible ?? true
        ];

        if (!empty($validated['adv_link_url'])) {
            $save['adv_link_url'] = $validated['adv_link_url'];
        }

        // $manager = new ImageManager(new Driver()); // 不再直接使用 ImageManager
        $saveDir = 'adv'; // 圖片儲存的子目錄，相對於 storage/app/public

        // 處理 adv_img_url，並依 cat_params 限制尺寸
        if (isset($validated['adv_img_url']) && $request->hasFile('adv_img_url')) {
            $file = $request->file('adv_img_url');

            // 從分類參數中獲取桌面版圖片的目標寬高，若無則使用預設值
            $desktopWidth = $fieldParams['adv_img_url']['width'] ?? 1200; // 預設寬度
            $desktopHeight = $fieldParams['adv_img_url']['height'] ?? 600; // 預設高度

            try {
                // 使用 ImageHelper 處理圖片：中心裁切至指定尺寸
                $processedImage = ImageHelper::processImage($file, $desktopWidth, $desktopHeight, 'center_crop');
                $extension = strtolower($file->getClientOriginalExtension());

                // 生成唯一的檔名
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename; // 完整的儲存路徑 (例如: adv/12345_unique.jpg)

                // 儲存處理後的圖片到 public 磁碟，品質 90，格式為 JPEG
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, $extension);

                $save['adv_img_url'] = $fullPath; // 將儲存路徑存入資料庫
            } catch (\Exception $e) {
                // 圖片處理或儲存失敗，記錄錯誤並返回
                return redirect()->back()->withInput()->with('error', '桌面版圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        // 處理 adv_img_m_url (行動版圖片)，並依 cat_params 限制尺寸
        if (isset($validated['adv_img_m_url']) && $request->hasFile('adv_img_m_url')) {
            $file = $request->file('adv_img_m_url');

            // 從分類參數中獲取行動版圖片的目標寬高，若無則使用預設值
            $mobileWidth = $fieldParams['adv_img_m_url']['width'] ?? 600; // 預設寬度
            $mobileHeight = $fieldParams['adv_img_m_url']['height'] ?? 300; // 預設高度

            try {
                // 使用 ImageHelper 處理圖片：中心裁切至指定尺寸
                $processedImage = ImageHelper::processImage($file, $mobileWidth, $mobileHeight, 'center_crop');
                $extension = strtolower($file->getClientOriginalExtension());

                // 生成唯一的檔名
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename; // 完整的儲存路徑

                // 儲存處理後的圖片到 public 磁碟，品質 90，格式為 JPEG
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, $extension);

                $save['adv_img_m_url'] = $fullPath; // 將儲存路徑存入資料庫
            } catch (\Exception $e) {
                // 圖片處理或儲存失敗，記錄錯誤並返回
                return redirect()->back()->withInput()->with('error', '行動版圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        $advert = Advert::create($save);

        // 建立 desc：前端應傳 desc[lang_id][adv_name]
        if ($request->has('desc') && is_array($request->desc)) {
            foreach ($request->desc as $lang_id => $desc) {
                // 若 adv_name 為空則略過；若要強制每個語系必填可在驗證時加入 rules
                if (!empty($desc['adv_name'])) {
                    AdvertDesc::create([
                        'adv_id' => $advert->adv_id,
                        'lang_id' => $lang_id,
                        'adv_name' => $desc['adv_name'],
                    ]);
                }
            }
        }

        ContentHelper::showMsg(
            0,
            '廣告新增完成',
            [
                ['text' => '繼續新增', 'href' => route('admin.advert.create')],
                ['text' => '返回列表', 'href' => route('admin.advert.index')],
            ],
            true
        );
        return redirect()->back();
    }

    // 編輯表單
    public function edit(Advert $advert)
    {
        $cats = AdvertCategory::with('descs')->where('is_visible', 1)->get();
        $langs = Language::where('enabled', 1)->orderBy('display_order', 'desc')->get();
        $advert->load('descs');
        $isEdit = $advert->exists;

        // 轉成以 lang_id 為 key 的陣列，便於 blade 填值
        $descMap = [];
        foreach ($advert->descs as $desc) {
            // 確保 $desc->content 不是 null，避免 ContentHelper::decodeSiteUrl 報錯
            $desc->content = ContentHelper::decodeSiteUrl($desc->content ?? '');
            $descMap[$desc->lang_id] = $desc;
        }
        return $this->view('admin.advert.form', compact('advert', 'isEdit', 'cats', 'langs', 'descMap'));
    }

    // 更新
    public function update(Request $request, Advert $advert)
    {
        // 重新載入分類，以確保取得最新的 cat_params
        $category = AdvertCategory::findOrFail($request->cat_id ?? $advert->cat_id);
        $scope = (array) ($category->cat_func_scope ?? []);
        $catParams = (array) ($category->cat_params ?? []);
        $fieldParams = $catParams['fields'] ?? [];

        $rules = [
            'cat_id' => 'required|exists:advert_category,cat_id',
            'display_order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
        ];

        // 圖片驗證規則，如果已有圖片則允許為空 (不更新)，否則必須上傳
        if (in_array('adv_img_url', $scope)) {
            $rules['adv_img_url'] = $advert->adv_img_url ? 'nullable|image|mimes:jpg,jpeg,png|max:5120' : 'nullable|image|mimes:jpg,jpeg,png|max:5120';
        }

        if (in_array('adv_img_m_url', $scope)) {
            $rules['adv_img_m_url'] = $advert->adv_img_m_url ? 'nullable|image|mimes:jpg,jpeg,png|max:5120' : 'nullable|image|mimes:jpg,jpeg,png|max:5120';
        }

        // 其他欄位視 scope 決定
        if (in_array('adv_link_url', $scope)) {
            $rules['adv_link_url'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules);

        $advert->cat_id = $validated['cat_id'];
        $advert->display_order = $validated['display_order'] ?? 0;
        $advert->is_visible = $validated['is_visible'] ?? $advert->is_visible;

        // $manager = new ImageManager(new Driver()); // 不再直接使用 ImageManager
        $saveDir = 'adv'; // 圖片儲存的子目錄

        // 處理 adv_img_url (桌面版圖片)
        if ($request->hasFile('adv_img_url')) {
            $file = $request->file('adv_img_url');

            // 從分類參數中獲取桌面版圖片的目標寬高，若無則使用預設值
            $desktopWidth = $fieldParams['adv_img_url']['width'] ?? 1200;
            $desktopHeight = $fieldParams['adv_img_url']['height'] ?? 600;

            try {
                // 刪除舊的桌面版圖片檔案
                ImageHelper::deleteImage($advert->adv_img_url, 'public');

                // 使用 ImageHelper 處理圖片：中心裁切至指定尺寸
                $processedImage = ImageHelper::processImage($file, $desktopWidth, $desktopHeight, 'center_crop');
                $extension = strtolower($file->getClientOriginalExtension());

                // 生成唯一的檔名
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename;

                // 儲存處理後的圖片
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, $extension);

                $advert->adv_img_url = $fullPath; // 更新資料庫中的圖片路徑
            } catch (\Exception $e) {
                // 圖片處理或儲存失敗，記錄錯誤並返回
                return redirect()->back()->withInput()->with('error', '桌面版圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        // 處理 adv_img_m_url (行動版圖片)
        if ($request->hasFile('adv_img_m_url')) {
            $file = $request->file('adv_img_m_url');

            // 從分類參數中獲取行動版圖片的目標寬高，若無則使用預設值
            $mobileWidth = $fieldParams['adv_img_m_url']['width'] ?? 600;
            $mobileHeight = $fieldParams['adv_img_m_url']['height'] ?? 300;

            try {
                // 刪除舊的行動版圖片檔案
                ImageHelper::deleteImage($advert->adv_img_m_url, 'public');

                // 使用 ImageHelper 處理圖片：中心裁切至指定尺寸
                $processedImage = ImageHelper::processImage($file, $mobileWidth, $mobileHeight, 'center_crop');
                $extension = strtolower($file->getClientOriginalExtension());

                // 生成唯一的檔名
                $filename = ImageHelper::generateUniqueFilename($file);
                $fullPath = $saveDir . '/' . $filename;

                // 儲存處理後的圖片
                ImageHelper::saveProcessedImage($processedImage, $fullPath, 'public', 90, $extension);

                $advert->adv_img_m_url = $fullPath; // 更新資料庫中的圖片路徑
            } catch (\Exception $e) {
                // 圖片處理或儲存失敗，記錄錯誤並返回
                return redirect()->back()->withInput()->with('error', '行動版圖片處理或儲存失敗: ' . $e->getMessage());
            }
        }

        if (isset($validated['adv_link_url'])) {
            $advert->adv_link_url = $validated['adv_link_url'];
        }

        $advert->save();

        // 多語系處理
        if ($request->has('desc') && is_array($request->desc)) {
            foreach ($request->desc as $lang_id => $desc) {
                $existing = AdvertDesc::where('adv_id', $advert->adv_id)->where('lang_id', $lang_id)->first();

                if ($existing) {
                    $existing->update([
                        'adv_name' => $desc['adv_name'] ?? '',
                    ]);
                } else {
                    if (!empty($desc['adv_name'])) {
                        AdvertDesc::create([
                            'adv_id' => $advert->adv_id,
                            'lang_id' => $lang_id,
                            'adv_name' => $desc['adv_name']
                        ]);
                    }
                }
            }
        }

        ContentHelper::showMsg(
            0,
            '編輯操作完成',
            [
                ['text' => '繼續編輯', 'href' => route('admin.advert.edit', $advert->adv_id)],
                ['text' => '返回列表', 'href' => route('admin.advert.index')],
            ],
            true
        );

        return redirect()->back();
    }

    // 刪除
    public function destroy(Advert $advert)
    {
        // 刪除桌面版圖片檔案
        ImageHelper::deleteImage($advert->adv_img_url, 'public');

        // 刪除行動版圖片檔案
        ImageHelper::deleteImage($advert->adv_img_m_url, 'public');

        $advert->delete();

        return redirect()->route('admin.advert.index')->with('success', '廣告已刪除');
    }
}
