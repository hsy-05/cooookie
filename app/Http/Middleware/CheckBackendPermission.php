<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // ⭐ 關鍵
use App\Models\User;
use App\Helpers\ContentHelper;

class CheckBackendPermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $permission 路由傳入的權限 Key，例如 'news.create'
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
    // 讓 IDE 知道 Auth::user() 是 User
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasPermission($permission)) {

            ContentHelper::showMsg(
                1,
                '對不起，您沒有執行此項操作的權限！',
                [],
                true
            );

            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
