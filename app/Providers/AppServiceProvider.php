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
        // 定義全域 Gate，讓 Blade 可以用 @can('news.view')
        // 這裡會攔截所有 $user->can('...') 的呼叫
        Gate::before(function ($user, $ability) {
            // 這裡的邏輯：如果 user model 有定義 checkPermission 之類的方法
            // return $user->hasRole('admin') ? true : null; // 範例：超級管理員直接放行
            return method_exists($user, 'canDo') ? $user->canDo($ability) : null;
        });
    }
}
