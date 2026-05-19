<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Advert, AdvertDesc, AdvertCategory, Language};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class AdvertController extends BaseAdminController
{
    // 權限與頁面標題設定
    protected $permissionName = 'advert';
    protected $pageTitle = '廣告管理';

    /**
     * 基礎檔案配置 (作為預設值)
     * 實際尺寸會由 handleFileUploads 根據 cat_params 自動動態覆蓋
     */
    protected $pageCfg = [
        'files' => [
            'adv_img_url' => [
                'path' => 'adv',
                'mode' => 'center_crop', // 預設模式，若沒設定寬高則會自動跳過裁切
            ],
            'adv_img_m_url' => [
                'path' => 'adv',
                'mode' => 'center_crop',
            ],
        ],
    ];

    /**
     * 列表頁面
     * @param Request $request 包含搜尋與分頁參數
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $this->getPerPage($request);

        // 使用 with 減少資料庫查詢次數
        $advertList = Advert::with(['descs', 'category'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('descs', fn($q) => $q->where('adv_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('display_order')
            ->orderByDesc('adv_id')
            ->paginate($perPage);

        return $this->view('admin.advert.index', compact('advertList', 'search'));
    }

    /**
     * 顯示新增表單
     */
    public function create()
    {
        return $this->renderForm(new Advert());
    }

    /**
     * 執行儲存動作
     * @param Request $request
     */
    public function store(Request $request)
    {
        // 1. 驗證基礎欄位與動態 Scope 欄位
        $this->validateRequest($request);

        return DB::transaction(function () use ($request) {
            try {
                $advert = new Advert();

                // 2. 處理圖片上傳 (會自動抓取分類的寬高設定)
                $this->handleFileUploads($request, $advert);

                // 3. 儲存主表資料
                $advert->fill([
                    'cat_id'        => $request->cat_id,
                    'adv_link_url'  => $request->adv_link_url,
                    'display_order' => $request->display_order ?? 0,
                    'is_visible'    => $request->has('is_visible'),
                ])->save();

                // 4. 儲存多語系翻譯
                $this->saveTranslations($advert, $request->desc);

                // 5. 紀錄操作日誌
                $advert->writeLog('新增', $advert->currentDesc->adv_name ?? '未知名廣告');

                $backUrl = $request->input('back_url', route('admin.advert.index'));

                $this->showMsg(0, '新增完成', [
                    ['text' => '繼續新增', 'href' => route('admin.advert.create')],
                    ['text' => '繼續編輯', 'href' => route('admin.advert.edit', $advert->adv_id)],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Advert Store Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '新增失敗：' . $e->getMessage());
            }
        });
    }

    /**
     * 顯示編輯表單
     * @param Advert $advert
     */
    public function edit(Advert $advert)
    {
        return $this->renderForm($advert);
    }

    /**
     * 執行更新動作
     * @param Request $request
     * @param Advert $advert
     */
    public function update(Request $request, Advert $advert)
    {
        $this->validateRequest($request, $advert);

        return DB::transaction(function () use ($request, $advert) {
            try {
                // 處理圖片更新
                $this->handleFileUploads($request, $advert);

                // 更新主表
                $advert->update([
                    'cat_id'        => $request->cat_id,
                    'adv_link_url'  => $request->adv_link_url,
                    'display_order' => $request->display_order ?? 0,
                    'is_visible'    => $request->has('is_visible'),
                ]);

                // 更新翻譯
                $this->saveTranslations($advert, $request->desc);

                $advert->writeLog('編輯', $advert->currentDesc->adv_name ?? '未知名廣告');

                $backUrl = $request->input('back_url', route('admin.advert.index'));

                $this->showMsg(0, '編輯操作完成', [
                    ['text' => '繼續編輯', 'href' => route('admin.advert.edit', $advert->adv_id)],
                    ['text' => '返回列表', 'href' => $backUrl],
                ], true);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Advert Update Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '更新失敗');
            }
        });
    }

    /**
     * 刪除廣告 (含圖片實體檔案清理)
     * @param Advert $advert
     */
    public function destroy(Advert $advert)
    {
        $advert->load('currentDesc');
        $name = $advert->currentDesc->adv_name ?? '未知名廣告';

        // 呼叫 Model 內的 HasImageFields 特性自動清理檔案
        $advert->delete();

        $advert->writeLog('刪除', $name);

        return redirect()->route('admin.advert.index')->with('form_success_swal', '廣告已刪除');
    }

    /* --- 內部輔助方法 (符合專業開發交接規範) --- */
/**
     * 表單渲染通用邏輯
     * @param Advert $advert
     */
    private function renderForm(Advert $advert)
    {
        $isEdit = $advert->exists;

        // 抓取所有分類，前端 JS 會用到裡面的 cat_func_scope 與 cat_params
        $cats = AdvertCategory::with('descs')
            ->where('is_visible', 1)
            ->orderBy('display_order')
            ->get();

        $langs = Language::where('enabled', 1)->orderByDesc('display_order')->get();

        // --- 處理建議尺寸提示 (fileConfigs) ---
        // 取得當前的分類 ID（編輯時用廣告的，新增時預設用第一個分類）
        $currentCatId = $advert->cat_id ?? ($cats->first()->cat_id ?? null);
        $currentCat = $cats->where('cat_id', $currentCatId)->first();

        // 抓取該分類的 params 設定
        $catParams = $currentCat->cat_params['fields'] ?? [];

        // 建立符合 Blade 格式的 fileConfigs
        $fileConfigs = [];
        foreach ($this->pageCfg['files'] as $field => $config) {
            $fileConfigs[$field] = [
                'width'  => $catParams[$field]['width'] ?? null,
                'height' => $catParams[$field]['height'] ?? null,
            ];
        }

        $descMap = [];
        if ($isEdit) {
            $advert->load('descs');
            foreach ($advert->descs as $desc) {
                $descMap[$desc->lang_id] = $desc;
            }
        }

        // 預設返回按鈕路由
        $backUrl = $this->getBackUrl('admin.advert.index');

        // 記得把 fileConfigs 傳出去，這樣 Blade 第一次載入時才有值
        return $this->view('admin.advert.form', compact(
            'advert', 'isEdit', 'cats', 'langs', 'descMap', 'fileConfigs', 'backUrl'
        ));
    }

    /**
     * 驗證請求：根據分類的 cat_func_scope 動態決定哪些欄位必填
     * @param Request $request
     * @param Advert|null $advert
     */
    private function validateRequest(Request $request, $advert = null)
    {
        $category = AdvertCategory::findOrFail($request->cat_id);
        $scope = (array) ($category->cat_func_scope ?? []);

        // 基礎必填欄位
        $rules = [
            'cat_id'        => 'required|exists:advert_category,cat_id',
            'display_order' => 'nullable|integer',
            'desc'          => 'required|array',
            'desc.*.adv_name' => 'required|string|max:255',
        ];

        // 根據分類範圍動態增加驗證
        if (in_array('adv_img_url', $scope)) {
            // 新增時圖片必填，編輯時若已有圖則可選
            $rules['adv_img_url'] = ($advert && $advert->adv_img_url) ? 'nullable|image|max:5120' : 'required|image|max:5120';
        }
        if (in_array('adv_img_m_url', $scope)) {
            $rules['adv_img_m_url'] = 'nullable|image|max:5120';
        }
        if (in_array('adv_link_url', $scope)) {
            $rules['adv_link_url'] = 'nullable|string|max:1000';
        }

        $request->validate($rules);
    }

    /**
     * 智慧檔案上傳處理：自動從 cat_params 抓取尺寸，沒設定寬高則不限尺寸
     * @param Request $request
     * @param Advert $advert
     */
    private function handleFileUploads(Request $request, Advert $advert)
    {
        $category = AdvertCategory::find($request->cat_id);
        $fieldParams = $category->cat_params['fields'] ?? [];

        foreach ($this->pageCfg['files'] as $field => $config) {
            if ($request->hasFile($field)) {

                // 關鍵防呆：從分類參數抓取設定，如果沒有設定 width 或 height，就設為 null
                // ImageHelper 在收到 width/height 為 null 時，應會跳過裁切直接原圖儲存
                $config['width']  = $fieldParams[$field]['width'] ?? null;
                $config['height'] = $fieldParams[$field]['height'] ?? null;

                // 若完全沒設定寬高，則把處理模式改為單純上傳 (或由 ImageHelper 內部判定)
                if (is_null($config['width']) && is_null($config['height'])) {
                    $config['mode'] = 'original';
                }

                $advert->$field = ImageHelper::handleUpload(
                    $request->file($field),
                    $config['path'],
                    $advert->$field,
                    $config
                );
            }
        }
    }

    /**
     * 儲存語系資料
     * @param Advert $advert
     * @param array|null $descData
     */
    private function saveTranslations(Advert $advert, ?array $descData)
    {
        if (!$descData) return;

        foreach ($descData as $langId => $data) {
            if (empty($data['adv_name'])) {
                AdvertDesc::where('adv_id', $advert->adv_id)->where('lang_id', $langId)->delete();
                continue;
            }

            AdvertDesc::updateOrInsert(
                ['adv_id' => $advert->adv_id, 'lang_id' => $langId],
                [
                    'adv_name'   => $data['adv_name'],
                    'adv_subname' => $data['adv_subname'] ?? null,
                    'adv_brief' => $data['adv_brief'] ?? null,
                ]
            );
        }
    }

    /**
     * AJAX 刪除單一圖片欄位
     */
    public function deleteImageField(Request $request, Advert $advert)
    {
        return $this->deleteImageFieldGeneric($request, $advert);
    }
}
