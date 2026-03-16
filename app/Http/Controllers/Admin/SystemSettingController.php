<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemSetting;
use App\Helpers\{ImageHelper, TagHelper};
use App\Http\Requests\Admin\UpdateSystemSettingRequest;
use App\Models\{Language};
use Illuminate\Support\Facades\{DB, Cache};

class SystemSettingController extends BaseAdminController
{
    protected $pageTitle = '系統參數設定';

    /**
     * 顯示系統設定頁面
     */
    public function index()
    {
        $tabs = SystemSetting::with('children')
            ->where('parent_id', 0)
            ->orderBy('display_order', 'asc')
            ->get();

        $languages = Language::where('enabled', 1)->get();

        return view('admin.system_settings.index', compact('tabs', 'languages'));
    }

    /**
     * 批次更新所有系統設定
     * @param UpdateSystemSettingRequest $request 驗證後的請求物件
     */
    public function updateAll(UpdateSystemSettingRequest $request)
    {
        // 抓取所有合法的設定定義。這是「專業開發」的關鍵：
        // 我們以資料庫有的設定為準，而不是以 Request 傳來的為準
        $definitions = SystemSetting::whereNotNull('setting_key')->get();

        DB::transaction(function () use ($request, $definitions) {
            // 取得前端送來的所有文字類設定
            $settingsInput = $request->input('settings', []);

            foreach ($definitions as $item) {
                $key = $item->setting_key;

                /**
                 * 防呆與清空邏輯：
                 * 如果前端完全清空標籤，Request 裡就不會有這個 Key。
                 * 所以我們從 $settingsInput 抓取值，沒抓到就給 null。
                 */
                $value = $settingsInput[$key] ?? null;

                // 如果是標籤類型，透過 Helper 進行標準化處理 (清洗空格、去重、轉字串)
                // 這裡即便 $value 是 null，TagHelper 也會回傳 null，從而正確清空資料庫
                if ($item->type === 'tags') {
                    $value = TagHelper::toString($value);
                }

                /**
                 * 效能優化：
                 * 只有當前端傳來的值與資料庫原本的值不同時，才執行 SQL Update。
                 * 注意：這裡使用 getRawOriginal 避開了 Model Trait 的自動轉換干擾
                 */
                if ($item->getRawOriginal('setting_value') !== $value) {
                    SystemSetting::where('setting_key', $key)->update(['setting_value' => $value]);
                }
            }

            // 處理圖片上傳邏輯
            if ($request->hasFile('settings')) {
                foreach ($request->file('settings') as $key => $file) {
                    // 找出對應的定義以取得上傳路徑設定
                    $def = $definitions->where('setting_key', $key)->first();
                    if ($def) {
                        $path = ImageHelper::handleUpload($file, $def->upload_dir ?? 'settings', $def->getRawOriginal('setting_value'));
                        SystemSetting::where('setting_key', $key)->update(['setting_value' => $path]);
                    }
                }
            }
        });

        // 設定更新後，務必清除快取與語系相關的 Session，確保前台立即生效
        Cache::forget('site_settings');

        // 只要 Session Key 包含 'lang' 或 'locale' 的全部清掉
        $keys = collect(session()->all())->keys();
        $langKeys = $keys->filter(fn($key) => str_contains($key, 'lang') || str_contains($key, 'locale'));
        session()->forget($langKeys->toArray());

        return redirect()->back()->with('form_success_swal', '所有設定已成功儲存並同步至快取。');
    }
}
