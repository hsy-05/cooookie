<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 顯示登入頁面
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * 處理登入請求
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 驗證帳號密碼（Breeze 提供）
        $request->authenticate();

        // 防止 Session Fixation 攻擊
        $request->session()->regenerate();

        /**
         * 登入後導向說明：
         * 1. 如果有 intended URL（被 middleware 擋回來的）
         * 2. 沒有的話 → RouteServiceProvider::HOME (/admin)
         */
        return redirect()->intended(RouteServiceProvider::HOME);
        // return redirect()->intended(route('admin.dashboard', absolute: false));
        // return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * 登出
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // 清除 session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 回到登入頁（用 route，不寫死 URL）
        return redirect()->route('login');
    }
}
