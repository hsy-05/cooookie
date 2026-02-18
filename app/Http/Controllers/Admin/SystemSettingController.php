<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AdminSystemSetting;

class SystemSettingController extends BaseAdminController
{
    protected $pageTitle = '系統參數設定';

    public function index()
    {
        // 1. 抓取所有頁籤，並同時抓取底下的設定項 (Eager Loading)
        $tabs = AdminSystemSetting::with('children')
            ->where('parent_id', 0)
            ->orderBy('display_order', 'asc')
            ->get();

        return $this->view('admin.system_settings.index', compact('tabs'));
    }

    /**
     * 批次儲存設定
     */
    public function updateAll(Request $request)
    {
        $data = $request->input('settings'); // 接收 [key => value] 的陣列

        foreach ($data as $key => $value) {
            AdminSystemSetting::where('setting_key', $key)->update([
                'setting_value' => $value
            ]);
        }

        return redirect()->back()->with('success', '設定已更新，並已同步更新系統快取！');
    }
}
