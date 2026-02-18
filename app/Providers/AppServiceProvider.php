<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\AdminSystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 目前沒有需要在 container 註冊的服務
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 定義全域 Gate，讓 Blade 可以用 @can('news.view')
        // 這裡會攔截所有 $user->can('...') 的呼叫
        Gate::before(function ($user, $ability) {
            // 這裡的邏輯：如果 user model 有定義 checkPermission 之類的方法
            // return $user->hasRole('admin') ? true : null; // 範例：超級管理員直接放行
            return method_exists($user, 'canDo') ? $user->canDo($ability) : null;
        });

        // 防呆：先檢查是否有資料表（避免在安裝 migration 前報錯）
        if (Schema::hasTable('admin_system_settings')) {
            $settings = AdminSystemSetting::getAllSettings();

            // 將資料庫的設定 覆蓋 或 新增 到 Laravel config 中
            // 這樣你在任何地方用 config('site.image_max_size') 都能抓到
            foreach ($settings as $key => $value) {
                Config::set('site.' . $key, $value);
            }
        }
    }
}
