<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advert;
use App\Models\AdvertDesc;
use App\Models\AdvertCategory;
use App\Models\Language;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Helpers\ContentHelper;
use App\Http\Controllers\Admin\BaseAdminController;

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
        // dd($adverts);
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

        $manager = new ImageManager(new Driver());
        $saveDir = storage_path('app/public/adv');
        if (!file_exists($saveDir)) mkdir($saveDir, 0755, true);

        // 處理 adv_img_url，並依 cat_params 限制尺寸
        if (isset($validated['adv_img_url']) && $request->hasFile('adv_img_url')) {
            $file = $request->file('adv_img_url');
            $img = $manager->read($file);

            $desktopWidth = $fieldParams['adv_img_url']['width'] ?? null;
            $desktopHeight = $fieldParams['adv_img_url']['height'] ?? null;

            if ($desktopWidth && $desktopHeight) {
                $img = $img->coverDown($desktopWidth, $desktopHeight, 'center');
            }

            $filename = time() . '.' . $file->getClientOriginalExtension();
            $img->save($saveDir . '/' . $filename);
            $save['adv_img_url'] = 'adv/' . $filename;
        }

        // 處理 adv_img_m_url 類似
        if (isset($validated['adv_img_m_url']) && $request->hasFile('adv_img_m_url')) {
            $file = $request->file('adv_img_m_url');
            $img = $manager->read($file);

            $desktopWidth = $fieldParams['adv_img_m_url']['width'] ?? null;
            $desktopHeight = $fieldParams['adv_img_m_url']['height'] ?? null;

            if ($desktopWidth && $desktopHeight) {
                $img = $img->coverDown($desktopWidth, $desktopHeight, 'center');
            }

            $filename = time() . '.' . $file->getClientOriginalExtension();
            $img->save($saveDir . '/' . $filename);

            $save['adv_img_m_url'] = 'adv/' . $filename;
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
            $descMap[$desc->lang_id] = $desc;
        }
        return $this->view('admin.advert.form', compact('advert', 'isEdit', 'cats', 'langs', 'descMap'));
    }

    // 更新
    public function update(Request $request, Advert $advert)
    {
        $category = AdvertCategory::findOrFail($request->cat_id ?? $advert->cat_id);
        $scope = (array) ($category->cat_func_scope ?? []);
        $catParams = (array) ($category->cat_params ?? []);
        $fieldParams = $catParams['fields'] ?? [];

        $rules = [
            'cat_id' => 'required|exists:advert_category,cat_id',
            'display_order' => 'nullable|integer',
            'is_visible' => 'nullable|boolean',
        ];

        if (in_array('adv_img_url', $scope)) {
            $rules['adv_img_url'] = $advert->adv_img_url ? 'nullable|image|mimes:jpg,jpeg,png|max:5120' : 'required|image|mimes:jpg,jpeg,png|max:5120';
        }

        if (in_array('adv_img_m_url', $scope)) {
            $rules['adv_img_m_url'] = $advert->adv_img_m_url ? 'nullable|image|mimes:jpg,jpeg,png|max:5120' : 'required|image|mimes:jpg,jpeg,png|max:5120';
        }

        if (in_array('adv_link_url', $scope)) {
            $rules['adv_link_url'] = 'required|string|max:1000';
        } else {
            $rules['adv_link_url'] = 'nullable|string|max:1000';
        }

        $validated = $request->validate($rules);

        $advert->cat_id = $validated['cat_id'];
        $advert->display_order = $validated['display_order'] ?? 0;
        $advert->is_visible = $validated['is_visible'] ?? $advert->is_visible;

        $manager = new ImageManager(new Driver());
        $saveDir = storage_path('app/public/adv');
        if (!file_exists($saveDir)) mkdir($saveDir, 0755, true);

        // 圖片處理（若有新圖，存新圖並刪舊圖）
        if ($request->hasFile('adv_img_url')) {
            $file = $request->file('adv_img_url');
            $img = $manager->read($file);

            $desktopWidth = $fieldParams['adv_img_url']['width'] ?? null;
            $desktopHeight = $fieldParams['adv_img_url']['height'] ?? null;

            if ($desktopWidth && $desktopHeight) {
                $img = $img->coverDown($desktopWidth, $desktopHeight, 'center');
            }

            $filename = time() . '.' . $file->getClientOriginalExtension();
            $img->save($saveDir . '/' . $filename);

            // 刪除舊圖
            if ($advert->adv_img_url && file_exists(storage_path('app/public/' . $advert->adv_img_url))) {
                @unlink(storage_path('app/public/' . $advert->adv_img_url));
            }

            $advert->adv_img_url = 'adv/' . $filename;
        }

        // 圖片處理（若有新圖，存新圖並刪舊圖）
        if ($request->hasFile('adv_img_m_url')) {
            $file = $request->file('adv_img_m_url');
            $img = $manager->read($file);

            $mobileWidth = $fieldParams['adv_img_m_url']['width'] ?? null;
            $mobileHeight = $fieldParams['adv_img_m_url']['height'] ?? null;

            if ($mobileWidth && $mobileHeight) {
                $img = $img->coverDown($mobileWidth, $mobileHeight, 'center');
            }

            $filename = time() . '.' . $file->getClientOriginalExtension();
            $img->save($saveDir . '/' . $filename);

            // 刪除舊圖
            if ($advert->adv_img_m_url && file_exists(storage_path('app/public/' . $advert->adv_img_m_url))) {
                @unlink(storage_path('app/public/' . $advert->adv_img_m_url));
            }

            $advert->adv_img_m_url = 'adv/' . $filename;
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
        if ($advert->adv_img_url && file_exists(storage_path('app/public/' . $advert->adv_img_url))) {
            @unlink(storage_path('app/public/' . $advert->adv_img_url));
        }

        if ($advert->adv_img_m_url && file_exists(storage_path('app/public/' . $advert->adv_img_m_url))) {
            @unlink(storage_path('app/public/' . $advert->adv_img_m_url));
        }

        $advert->delete();

        return redirect()->route('admin.advert.index')->with('success', '廣告已刪除');
    }
}
