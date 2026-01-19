<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. 取得資料庫版本
        try {
            $dbVersion = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Exception $e) {
            $dbVersion = '無法連線或未設定';
        }

        /**
         * 2. 系統資訊定義
         * 每一筆都是「結構化資料」，而不是單純 key => value
         *
         * 說明：
         * - label  ：畫面顯示名稱
         * - value  ：實際值
         * - type   ：顯示類型（text / badge）
         * - status ：狀態值（給 badge 用）
         */
        $systemInfo = [
            [
                'label' => '伺服器作業系統',
                'value' => php_uname('s') . ' ' . php_uname('r'),
                'type'  => 'text',
            ],
            [
                'label' => 'Web 伺服器',
                'value' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'type'  => 'text',
            ],
            [
                'label' => 'PHP 版本',
                'value' => PHP_VERSION,
                'type'  => 'text',
            ],
            [
                'label' => 'Laravel 版本',
                'value' => App::version(),
                'type'  => 'text',
            ],
            [
                'label' => '資料庫版本',
                'value' => $dbVersion,
                'type'  => 'text',
            ],
            [
                'label' => '郵件驅動 (Mail)',
                'value' => config('mail.default'),
                'type'  => 'text',
            ],
            [
                'label' => 'GD 圖片函式庫',
                'value' => extension_loaded('gd')
                    ? '啟用 (' . gd_info()['GD Version'] . ')'
                    : '未啟用',
                'type'  => 'text',
            ],
            [
                'label' => '檔案上傳限制',
                'value' => ini_get('upload_max_filesize'),
                'type'  => 'text',
            ],
            [
                'label' => '最大 POST 限制',
                'value' => ini_get('post_max_size'),
                'type'  => 'text',
            ],
            [
                'label' => '時區 (Timezone)',
                'value' => config('app.timezone') . ' (' . date('P') . ')',
                'type'  => 'text',
            ],

            // ===== 狀態型資料（Badge）=====
            [
                'label'  => '執行環境 (Env)',
                'value'  => App::environment(),
                'type'   => 'badge',
                'status' => App::environment(), // local / production
            ],
            [
                'label'  => '除錯模式 (Debug)',
                'value'  => config('app.debug') ? '開啟 (True)' : '關閉 (False)',
                'type'   => 'badge',
                'status' => config('app.debug'), // true / false
            ],
        ];

        /**
         * 3. 平均切成兩欄（給畫面並排用）
         * 之後項目加減，View 不用改
         */
        $systemInfoChunks = collect($systemInfo)->chunk(
            ceil(count($systemInfo) / 2)
        );

        return view('admin.dashboard', compact('systemInfoChunks'));
    }
}
