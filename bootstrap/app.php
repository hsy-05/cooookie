<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // 引入 Request
use Symfony\Component\HttpKernel\Exception\HttpException; // 引入 HttpException

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 當管理員「已登入」卻嘗試進入 login 或 register 頁面時，強制轉向到指定路徑
        $middleware->redirectUsersTo('/admin');

        // 註冊兩個 Laravel 預設 middleware
        $middleware->alias([
            'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
            'cookies' => \App\Http\Middleware\EncryptCookies::class,
            'admin.perm' => \App\Http\Middleware\CheckBackendPermission::class,
            'admin.theme' => \App\Http\Middleware\AdminThemeMiddleware::class,
        ]);

        // 加入 Laravel web group middleware 堆疊
        $middleware->web(prepend: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\SetLocale::class, // 語系
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 渲染異常時的邏輯
        $exceptions->render(function (HttpException $e, Request $request) {
            // 如果狀態碼是 419 (CSRF 過期)
            if ($e->getStatusCode() === 419) {
                // 專業做法：導回登入頁，並帶上一個閃存訊息 (Flash Message)
                return redirect()->route('login')->with('warning', '頁面已過期，請重新登入。');
            }
        });
    })->create();
