<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ContentHelper;

class CheckBackendPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 檢查權限
        if (!$user->canDo($permission)) {

            // 取得「前一頁」網址，如果沒有前一頁就去首頁 (防呆)
            $url = url()->previous();
            if ($url === url()->current()) {
                $url = route('admin.dashboard');
            }

            // 1. 呼叫 Helper 把訊息存入 Session (Flash)
            // 這裡的 $links 我們帶入剛剛算出來的 $url
            ContentHelper::showMsg(
                1,
                '對不起，您沒有執行此項操作的權限！',
                [['text' => '返回', 'href' => $url]],
                true
            );

            // 2. ⭐ 執行跳轉回原本所在的頁面
            // 使用 withInput() 可以確保如果是表單提交被攔截，原本打的字不會不見 (細心的小技巧)
            return redirect($url)->withInput();
        }

        return $next($request);
    }
}
