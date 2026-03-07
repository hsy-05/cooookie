<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 註冊應用程式服務
     * 通常用於綁定 Interface 或處理不涉及資料庫的基礎設定
     */
    public function register(): void
    {
        //
    }

    /**
     * 啟動應用程式服務
     * 這裡處理全站邏輯、權限定義與資料庫初始化設定
     */
    public function boot(): void
    {
        // 處理權限邏輯：在檢查所有權限前先執行的攔截器
        Gate::before(function ($user, $ability) {
            // 如果 User Model 有定義 canDo 方法則執行，否則回傳 null 續行檢查
            return method_exists($user, 'canDo') ? $user->canDo($ability) : null;
        });

        // 防呆設計：確保資料庫表存在才執行，避免在執行 Migration 期間噴錯
        if (Schema::hasTable('system_settings')) {

            // 取得所有設定值 (已在 Model 處理好快取)
            $settings = SystemSetting::getAllSettings();

            // 將資料庫設定動態注入 config 系統，方便全站調用
            foreach ($settings as $key => $value) {
                Config::set('site.' . $key, $value);
            }

            // 針對後端 AdminLTE 介面進行動態文字替換
            if (isset($settings['admin_site_name'])) {
                $siteName = $settings['admin_site_name'];
                Config::set('adminlte.logo', "<b>{$siteName}</b>");
                Config::set('adminlte.title_postfix', " - {$siteName}");
            }

            // 將設定檔直接注入前台佈局檔
            // 這樣前台 layout 就能直接使用 $sys 變數，不需要寫 @php 抓取
            View::composer('frontend.layouts.app', function ($view) {
                $view->with('sys', config('site'));
            });
        }
    }
}
