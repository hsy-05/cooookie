<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Helpers\ImageHelper;

class SystemSettingController extends BaseAdminController
{
    protected $pageTitle = '系統參數設定';

    public function index()
    {
        // 1. 抓取所有頁籤，並同時抓取底下的設定項 (Eager Loading)
        $tabs = SystemSetting::with('children')
            ->where('parent_id', 0)
            ->orderBy('display_order', 'asc')
            ->get();

        return $this->view('admin.system_settings.index', compact('tabs'));
    }
    /**
     * 批次儲存設定
     * * @param Request $request 包含 settings 陣列與可能的文件上傳
     */
    public function updateAll(Request $request)
    {
        // 1. 取得所有設定的定義，用來判斷 type
        $settingsDefinitions = SystemSetting::whereNotNull('setting_key')->get()->keyBy('setting_key');

        // 2. 處理文字、數字、單選等一般資料
        $inputData = $request->input('settings', []);
        foreach ($inputData as $key => $value) {
            SystemSetting::where('setting_key', $key)->update(['setting_value' => $value]);
        }

        // 3. 處理圖片與檔案上傳 (防呆：檢查是否有檔案進來)
        if ($request->hasFile('settings')) {
            foreach ($request->file('settings') as $key => $file) {
                $definition = $settingsDefinitions->get($key);
                if ($definition && in_array($definition->type, ['image', 'file'])) {
                    // 調用 ImageHelper，若為 image 則處理，file 則直接存
                    $path = ImageHelper::handleUpload(
                        $file,
                        $definition->upload_dir ?? 'settings',
                        $definition->setting_value // 傳入舊路徑供刪除
                    );

                    SystemSetting::where('setting_key', $key)->update(['setting_value' => $path]);
                }
            }
        }

        return redirect()->back()->with('success', '系統設定已更新！');
    }
}
