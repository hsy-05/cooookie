<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    /**
     * 顯示系統紀錄頁面
     */
    public function index()
    {
        // 傳遞參數到頁面：例如頁面標題
        $data = [
            'page_title' => '系統紀錄',
            'log_url' => url('log-viewer'), // 套件的原始路徑
        ];

        return view('admin.system.logs', $data);
    }
}
