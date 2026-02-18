<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Advert, AdvertDesc, AdvertCategory, Language};
use Illuminate\Support\Facades\{DB, Log, Auth};
use App\Helpers\{ContentHelper, ImageHelper};

class AdvertController extends BaseAdminController
{
    // 定義這個 Controller 屬於哪組權限與標題
    protected $permissionName = 'advert';
    protected $pageTitle = '廣告管理';

    /**
     * 頁面相關配置
     */
    protected $pageCfg = [
        // 定義哪些欄位需要處理檔案上傳
        'files' => [
            'adv_img_url' => [
                'path'   => 'adv',             // 儲存路徑
                'width'  => 1200,               // 寬度 (若不縮圖可設為 null)
                'height' => 600,               // 高度
                'mode'   => 'center_crop',     // 處理模式：center_crop, scale_fit
                'useOriginalName' => false,    // 是否使用原檔名 (false 代表自動生成唯一名稱)
            ],
            'adv_img_m_url' => [
                'path'   => 'adv',     // 儲存路徑
                'width'  => 375,               // 寬度 (若不縮圖可設為 null)
                'height' => 750,               // 高度
                'mode'   => 'center_crop',     // 處理模式：center_crop, scale_fit
                'useOriginalName' => false,    // 是否使用原檔名 (false 代表自動生成唯一名稱)
            ],
            // 未來若有 PDF 或 縮圖，直接在這裡增加一組設定即可
        ],
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');

        // 取得統一的分頁數
        $perPage = $this->getPerPage($request);

        // 使用 Eager Loading (with) 減少資料庫查詢壓力
        $advertList = Advert::with(['descs', 'category'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('descs', fn($q) => $q->where('adv_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('display_order')
            ->orderByDesc('adv_id')
            ->paginate($perPage);

        return $this->view('admin.advert.index', compact('advertList', 'search'));
    }

    public function create()
    {
        return $this->renderForm(new Advert());
    }

    public function store(Request $request)
    {
        // 基礎驗證 (包含動態欄位判斷)
        $this->validateRequest($request);

        return DB::transaction(function () use ($request) {
            try {
                $advert = new Advert();

                // 處理檔案/圖片上傳 (自動抓取分類參數修正尺寸)
                $this->handleFileUploads($request, $advert);

                // 儲存主表資料
                $advert->fill([
                    'cat_id'        => $request->cat_id,
                    'adv_link_url'  => $request->adv_link_url,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ])->save();

                // 儲存多語系資料 (廣告名稱)
                $this->saveTranslations($advert, $request->desc);

                // 紀錄操作日誌
                $advert->writeLog('新增', $advert->desc->adv_name ?? '未知名廣告');

                // 成功回傳 (使用自定義 ContentHelper)
                ContentHelper::showMsg(0, '新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.advert.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.advert.edit', $advert->adv_id)],
                    ['text' => '返回列表', 'href' => route('admin.advert.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Advert Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
            }
        });
    }

    public function edit(Advert $advert)
    {
        return $this->renderForm($advert);
    }

    public function update(Request $request, Advert $advert)
    {
        $this->validateRequest($request, $advert);

        return DB::transaction(function () use ($request, $advert) {
            try {
                // 處理檔案/圖片更新
                $this->handleFileUploads($request, $advert);

                // 更新主表
                $advert->update([
                    'cat_id'        => $request->cat_id,
                    'adv_link_url'  => $request->adv_link_url,
                    'is_visible'    => $request->has('is_visible'),
                    'display_order' => $request->display_order ?? 0,
                ]);

                // 更新多語系資料
                $this->saveTranslations($advert, $request->desc);

                $advert->writeLog('編輯', $advert->desc->adv_name ?? '未知名廣告');

                ContentHelper::showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.advert.edit', $advert->adv_id)],
                    ['text' => '返回列表', 'href' => route('admin.advert.index')],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Advert Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    public function destroy(Advert $advert)
    {
        // 先抓取名稱供 Log 使用
        $advert->load('desc');
        $name = $advert->desc->adv_name ?? '未知名廣告';

        // 刪除相關聯的所有實體檔案 (根據 pageCfg 自動清理)
        foreach (array_keys($this->pageCfg['files']) as $field) {
            if ($advert->$field) {
                ImageHelper::deleteImage($advert->$field, 'public');
            }
        }

        // 刪除資料庫紀錄
        AdvertDesc::where('adv_id', $advert->adv_id)->delete();
        $advert->delete();

        $advert->writeLog('刪除', $name);

        return redirect()->route('admin.advert.index')->with('form_success_swal', '廣告已刪除');
    }

    /* --- 內部輔助方法 (Private Helper Methods) --- */

    /**
     * 渲染表單通用邏輯
     */
    private function renderForm(Advert $advert)
    {
        $isEdit = (bool)$advert->exists;
        $cats = AdvertCategory::with('descs')->where('is_visible', 1)->orderBy('display_order')->get();
        $langs = Language::where('enabled', 1)->orderByDesc('display_order')->get();

        // 將配置傳給前端，以便顯示建議尺寸提示
        $fileConfigs = $this->pageCfg['files'];

        $descMap = [];
        if ($isEdit) {
            $advert->load('descs');
            foreach ($advert->descs as $desc) {
                $descMap[$desc->lang_id] = $desc;
            }
        }

        return $this->view('admin.advert.form', compact('advert', 'isEdit', 'cats', 'langs', 'descMap', 'fileConfigs'));
    }

    /**
     * 驗證請求 (動態結合分類 Scope)
     */
    private function validateRequest(Request $request, $advert = null)
    {
        $rules = [
            'cat_id'        => 'required|exists:advert_category,cat_id',
            'is_visible'    => 'nullable|boolean',
            'display_order' => 'nullable|integer',
            'desc'          => 'nullable|array',
            'desc.*.adv_name' => 'required_with:desc.*|string|max:255',
        ];

        // 根據分類配置，動態增加圖片與連結的驗證規則
        if ($request->cat_id) {
            $category = AdvertCategory::find($request->cat_id);
            $scope = $category->cat_func_scope ?? [];

            if (in_array('adv_img_url', $scope)) {
                $rules['adv_img_url'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';
            }
            if (in_array('adv_img_m_url', $scope)) {
                $rules['adv_img_m_url'] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120';
            }
            if (in_array('adv_link_url', $scope)) {
                $rules['adv_link_url'] = 'nullable|string|max:1000';
            }
        }

        $request->validate($rules);
    }

    /**
     * 萬用檔案上傳處理邏輯
     * 這裡加入了廣告特有的邏輯：從分類參數 (cat_params) 動態覆蓋配置尺寸
     */
    private function handleFileUploads(Request $request, Advert $advert)
    {
        $category = AdvertCategory::find($request->cat_id);
        $fieldParams = $category->cat_params['fields'] ?? [];

        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {
                // 如果分類有設定特定尺寸，就覆蓋預設的 $pageCfg
                if (isset($fieldParams[$field])) {
                    $config['width'] = $fieldParams[$field]['width'] ?? $config['width'];
                    $config['height'] = $fieldParams[$field]['height'] ?? $config['height'];
                }

                $advert->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $advert->$field, // 傳入舊路徑以供刪除
                    $config
                );
            }
        }
    }

    /**
     * 儲存/更新多語系描述
     */
    private function saveTranslations(Advert $advert, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            if (empty($data['adv_name'])) {
                AdvertDesc::where('adv_id', $advert->adv_id)->where('lang_id', $langId)->delete();
                continue;
            }

            DB::table('advert_desc')->updateOrInsert(
                ['adv_id' => $advert->adv_id, 'lang_id' => $langId],
                [
                    'adv_name'   => $data['adv_name'],
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * AJAX 刪除單一圖片欄位 (通用方法)
     */
    public function deleteImageField(Request $request, Advert $advert)
    {
        // 調用 BaseAdminController 中的通用邏輯
        return $this->deleteImageFieldGeneric($request, $advert);
    }
}
