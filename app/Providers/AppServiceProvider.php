<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::share('BASE_URL', url('/'));
        View::share('UPLOAD_PATH', url('storage'));
        View::share('ADMIN_URL', url('admin'));
    }
}
