<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 使用者登入後的預設導向位置
     *
     * Breeze / Auth / redirect()->intended()
     * 都會使用這個常數
     */

    // 這裡改的是「Laravel 核心系統(如登入完成後)」自動要把人送到哪裡
    public const HOME = '/admin';
}
