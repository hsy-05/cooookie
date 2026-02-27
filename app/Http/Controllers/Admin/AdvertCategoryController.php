<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AdvertCategory, AdvertCategoryDesc};
use Illuminate\Support\Facades\{DB, Log};
use App\Helpers\{ContentHelper, ImageHelper};

class AdvertCategoryController extends BaseAdminController
{
    // 1. 基本配置：只要寫這兩行，BaseAdminController 就會幫你處理好權限檢查與頁面標題
    protected $permissionName = 'advert_category';
    protected $pageTitle = '廣告分類管理';

    /**
     * 廣告分類列表
     * * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 使用 Base 的 getPerPage，這會自動記憶使用者的分頁選擇 (8, 20, 50...)
        $perPage = $this->getPerPage($request);

        $list = AdvertCategory::with(['desc'])
            ->orderByDesc('display_order')
            ->orderByDesc('cat_id')
            ->paginate($perPage);

        return $this->view('admin.advert_category.index', compact('list'));
    }

    /**
     * 新增表單頁
     */
    public function create()
    {
        return $this->renderForm(new AdvertCategory([
            'cat_func_scope' => ['adv_img_url', 'adv_img_m_url', 'adv_link_url'], // 預設勾選
            'is_visible'     => true,
            'display_order'  => 0,
        ]));
    }

    /**
     * 執行儲存動作
     * * @param Request $request
     */
    public function store(Request $request)
    {
        return $this->processSave($request, new AdvertCategory());
    }

    /**
     * 編輯表單頁
     * * @param AdvertCategory $advertCategory Laravel 會自動根據 ID 尋找 Model
     */
    public function edit(AdvertCategory $advertCategory)
    {
        return $this->renderForm($advertCategory);
    }

    /**
     * 執行更新動作
     */
    public function update(Request $request, AdvertCategory $advertCategory)
    {
        return $this->processSave($request, $advertCategory);
    }

    /**
     * 刪除分類
     */
    public function destroy(AdvertCategory $advertCategory)
    {
        $title = $advertCategory->title;

        // 這裡會自動觸發 HasImageFields 刪除相關檔案 (如果有)
        $advertCategory->delete();

        // 寫入日誌
        $advertCategory->writeLog('刪除', $title);

        return redirect()->route('admin.advert_category.index')->with('form_success_swal', '分類已成功刪除');
    }

    /* --- 內部私有方法：維持 Controller 簡潔的關鍵 --- */

    /**
     * 統一渲染表單
     * * @param AdvertCategory $category
     */
    private function renderForm(AdvertCategory $category)
    {
        $isEdit = $category->exists;
        $langs = $this->getActiveLanguages();

        // 取得多語系資料並轉為以 lang_id 為 Key 的 map，方便 Blade 填值
        $descMap = $isEdit ? $category->descs->keyBy('lang_id')->all() : [];

        return $this->view('admin.advert_category.form', compact('category', 'isEdit', 'langs', 'descMap'));
    }

    /**
     * 統一處理 新增/更新 的邏輯
     * * @param Request $request
     * @param AdvertCategory $category
     */
    private function processSave(Request $request, AdvertCategory $category)
    {
        // 1. 驗證資料
        $validated = $request->validate([
            'cat_code'      => 'required|string|max:50|unique:advert_category,cat_code,' . ($category->cat_id ?? 'NULL') . ',cat_id',
            'display_order' => 'nullable|integer',
            'desc'          => 'required|array', // 強制要求至少要填一個語系的名稱
        ]);

        return DB::transaction(function () use ($request, $category) {
            try {
                // 2. 填充基本屬性
                $category->cat_code       = $request->cat_code;
                $category->cat_func_scope = $request->input('cat_func_scope', []);

                // 處理 cat_params: 如果是字串則 decode，如果是陣列則直接存 (受惠於 Model casts)
                $params = $request->input('cat_params');
                $category->cat_params = is_string($params) ? json_decode($params, true) : $params;

                $category->display_order  = $request->input('display_order', 0);
                $category->is_visible     = $request->has('is_visible');
                $category->save();

                // 3. 處理多語系名稱
                $this->saveTranslations($category, $request->input('desc'));

                // 4. 紀錄日誌
                $action = $category->wasRecentlyCreated ? '新增' : '編輯';
                $category->writeLog($action, $category->title);

                // 5. 提示訊息與跳轉 (使用 ContentHelper 維持 UX 一致性)
                ContentHelper::showMsg(0, "分類{$action}完成", [
                    ['text' => '返回列表', 'href' => route('admin.advert_category.index')],
                    ['text' => '繼續編輯', 'href' => route('admin.advert_category.edit', $category->cat_id)],
                ]);

                return redirect()->back();

            } catch (\Exception $e) {
                Log::error("AdvertCategory Save Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '儲存失敗，請檢查輸入內容');
            }
        });
    }

    /**
     * 儲存語系資料
     * * @param AdvertCategory $category
     * @param array $descData
     */
    private function saveTranslations(AdvertCategory $category, array $descData)
    {
        foreach ($descData as $langId => $data) {
            // 如果沒填名稱，就當作不啟用該語系，刪除舊有描述
            if (empty($data['cat_name'])) {
                AdvertCategoryDesc::where('cat_id', $category->cat_id)->where('lang_id', $langId)->delete();
                continue;
            }

            // 使用「有則更新、無則新增」
            AdvertCategoryDesc::updateOrInsert(
                ['cat_id' => $category->cat_id, 'lang_id' => $langId],
                ['cat_name' => $data['cat_name']]
            );
        }
    }
}
