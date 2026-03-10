<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * 語系設定中間件
 * 負責根據環境（前後台）自動切換系統語系與資料庫查詢 ID
 */
class SetLocale
{
    /**
     * 處理語系邏輯
     * * @param Request $request 當前請求物件
     * @param Closure $next 下一個執行層級
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 判斷當前是否為後台路徑
        $isAdmin = $request->is('admin') || $request->is('admin/*');

        // 根據環境選取正確的 Session 名稱與 Config 設定鍵
        $sessionKey = $isAdmin ? 'admin_locale' : 'front_locale';
        $sessionIdKey = $isAdmin ? 'admin_lang_id' : 'front_lang_id';
        $configKey = $isAdmin ? 'site.backend_default_lang' : 'site.frontend_default_lang';

        // 如果 Session 遺失或不存在，則從 Config 系統重新載入資料庫預設值
        if (!Session::has($sessionKey)) {
            $this->initializeLocale($sessionKey, $sessionIdKey, config($configKey, 1));
        }

        // 同步一個全域通用的 lang_id 給 Model Accessor 使用
        // 這樣 Model 就不需要自己判斷現在是在前台還是後台
        Session::put('lang_id', Session::get($sessionIdKey));

        // 執行全域廣播：設定 Laravel 本地化語系
        App::setLocale(Session::get($sessionKey));

        return $next($request);
    }

    /**
     * 初始化 Session 中的語系資料
     * * @param string $sessionKey 儲存語系代碼的 Key
     * @param string $sessionIdKey 儲存語系 ID 的 Key
     * @param int|string $defaultId 資料庫預設的語系 ID
     */
    private function initializeLocale(string $sessionKey, string $sessionIdKey, $defaultId)
    {
        $language = Language::where('lang_id', (int)$defaultId)
                            ->where('enabled', 1)
                            ->first();

        // 寫入 Session，若找不到語言則回傳系統 config 預設值
        Session::put($sessionKey, $language ? $language->code : config('app.locale'));
        Session::put($sessionIdKey, $language ? $language->lang_id : 1);
    }
}
