<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemSetting;
use App\Helpers\ImageHelper;
use App\Http\Requests\Admin\UpdateSystemSettingRequest;
use Illuminate\Support\Facades\DB;

class SystemSettingController extends BaseAdminController
{
	protected $pageTitle = '系統參數設定';

    /**
     * 顯示系統設定頁面
     */
    public function index()
    {
        // 抓取 parent_id 為 0 的作為頁籤，並預加載子項目 (Eager Loading)
        $tabs = SystemSetting::with('children')
            ->where('parent_id', 0)
            ->orderBy('display_order', 'asc')
            ->get();

        return view('admin.system_settings.index', compact('tabs'));
    }

    /**
     * 批次更新所有系統設定
     * @param UpdateSystemSettingRequest $request 驗證後的請求物件
     */
    public function updateAll(UpdateSystemSettingRequest $request)
    {
        // 抓取所有合法的 Key 定義，避免非法注入
        $definitions = SystemSetting::whereNotNull('setting_key')->get()->keyBy('setting_key');

        DB::transaction(function () use ($request, $definitions) {
            $settingsInput = $request->input('settings', []);

            foreach ($settingsInput as $key => $value) {
                if (!$definitions->has($key)) continue;

                $item = $definitions[$key];

                // 處理標籤類型：送來的是 Array，存入資料庫前須轉為逗號字串
                if ($item->type === 'tags') {
                    $value = is_array($value) ? implode(',', array_filter($value)) : '';
                }

                // 只有值有變動時才更新，優化效能
                SystemSetting::where('setting_key', $key)->update(['setting_value' => $value]);
            }

            // 處理圖片上傳邏輯
            if ($request->hasFile('settings')) {
                foreach ($request->file('settings') as $key => $file) {
                    if ($definitions->has($key)) {
                        $def = $definitions[$key];
                        $path = ImageHelper::handleUpload($file, $def->upload_dir ?? 'settings', $def->setting_value);
                        SystemSetting::where('setting_key', $key)->update(['setting_value' => $path]);
                    }
                }
            }
        });

        return redirect()->back()->with('form_success_swal', '所有設定已成功儲存並同步至快取。');
    }
}
