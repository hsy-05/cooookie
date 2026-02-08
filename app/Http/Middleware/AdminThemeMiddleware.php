<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AdminThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. 確保已登入
        if (Auth::check()) {
            /** @var \App\Models\User $user */ // <--- 加入這一行註解，紅線就會消失
            $user = Auth::user();

            /* |--------------------------------------------------------------------------
            | 呼叫 User Model 的 getPreference 獲取設定
            |--------------------------------------------------------------------------
            | 第一個參數：JSON 裡的 Key 名稱
            | 第二個參數：如果沒設定時的「預設值」 (防呆核心)
            */

            // 深色模式
            Config::set('adminlte.layout_dark_mode', $user->getPreference('dark_mode', false));

            // 佈局
            Config::set('adminlte.layout_sidebar_collapse', $user->getPreference('sidebar_collapse', false));
            Config::set('adminlte.layout_fixed_sidebar', $user->getPreference('sidebar_fixed', true));

            // Navbar 顏色
            $navColor = $user->getPreference('navbar_color', 'navbar-white navbar-light');
            Config::set('adminlte.classes_topnav', 'navbar-expand ' . $navColor);

            // Sidebar 樣式
            $sideTheme = $user->getPreference('sidebar_theme', 'sidebar-dark-primary');
            Config::set('adminlte.classes_sidebar', $sideTheme . ' elevation-4');

            // 強調色與文字大小
            $accent = $user->getPreference('accent_color', 'accent-primary');
            $textSm = $user->getPreference('text_sm', false) ? 'text-sm' : '';
            Config::set('adminlte.classes_body', trim($textSm . ' ' . $accent));

            // 2. 計算客製化頁籤顏色
            // 將 accent-warning 轉換為 card-warning，維持視覺統一
            $cardColor = 'card-primary';
            if ($accent && str_starts_with($accent, 'accent-')) {
                $cardColor = str_replace('accent-', 'card-', $accent);
            }

            // 3. 關鍵：分享給全站 Blade
            view()->share('customCardClass', $cardColor . ' card-outline card-outline-tabs');
        }

        return $next($request);
    }
}
