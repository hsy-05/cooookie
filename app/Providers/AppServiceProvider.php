<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        /**
         * 全站共用的 View 變數
         * 傳統後台專案常見寫法，新手好理解
         */

        // 網站根目錄 URL
        View::share('BASE_URL', url('/'));

        // 上傳檔案存取路徑
        View::share('UPLOAD_PATH', url('storage'));

        // 後台根目錄 URL
        View::share('ADMIN_URL', url('admin'));

        // 定義全域 Gate，讓 Blade 可以用 @can('news.view')
        // 這裡會攔截所有 $user->can('...') 的呼叫
        Gate::before(function ($user, $ability) {
            // $ability 就是權限 Key，例如 'news.view'
            return $user->hasPermission($ability);
        });
    }
}
