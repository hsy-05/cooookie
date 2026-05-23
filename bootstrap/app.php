<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException; // 引入基礎 HttpException 即可
use App\Http\Controllers\Admin\BaseAdminController;

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

        // 統一攔截所有 HTTP 相關異常 (包含 404, 419, 403 等)
        $exceptions->render(function (HttpException $e, Request $request) {

            // 情況 1：狀態碼是 419 (CSRF 過期)
            if ($e->getStatusCode() === 419) {
                return redirect()->route('login')->with('warning', '頁面已過期，請重新登入。');
            }

            // 情況 2：狀態碼是 404 (找不到網頁) 且屬於後台網址
            if ($e->getStatusCode() === 404 && ($request->is('admin') || $request->is('admin/*'))) {

                // 1. 呼叫你的提示視窗 function (寫入 Session Flash)
                BaseAdminController::showMsg(
                    1, // 錯誤類型: 1=錯誤
                    '很抱歉，找不到您請求的後台網頁或該頁面已不存在。'
                );

                // 2. 轉向到後台儀表板首頁 (名稱為 admin.dashboard)
                // 這樣轉過去後，後台首頁就會抓到 session 快閃訊息並跳出提示窗
                return redirect()->route('admin.dashboard');
            }
        });

    })->create();
