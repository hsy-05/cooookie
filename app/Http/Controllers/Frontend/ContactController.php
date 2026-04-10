<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ContactRequest;
use App\Models\Contact;
use Illuminate\Support\Facades\{Mail, DB, Log, Http};
use Illuminate\Support\Str;
use App\Mail\ContactNotification;
use App\Helpers\{MailConfigHelper, ContentHelper};

class ContactController extends Controller
{
    /**
     * 顯示聯絡我們頁面
     */
    public function index()
    {
        // --- 麵包屑與 Title 處理 ---
        $crumbs = [['text' => '關於我們', 'href' => route('about')]];

        // 呼叫父類別方法，自動處理全站共享變數
        $this->setBreadcrumbs($crumbs);

        return view('frontend.contact');
    }

    /**
     * 處理聯絡表單提交
     * @param ContactRequest $request 已封裝驗證邏輯的請求物件
     */
    public function store(ContactRequest $request)
    {
        // --- 步驟 1：發送請求到 Google 伺服器驗證 Token ---
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        $resBody = $response->json();

        // --- 步驟 2：判斷 Google 驗證結果 ---
        // success 為 true 且分數大於 0.5 (Google 建議的門檻值，越高代表越像真人)
        if (!$resBody['success'] || $resBody['score'] < 0.5) {
            Log::warning("機器人攻擊預警：IP [{$request->ip()}] 驗證分數低於門檻。");
            return response()->json(['success' => false, 'message' => '系統偵測到異常流量，請稍後再試。'], 403);
        }

        // --- 步驟 3：資料處理與入庫 ---
        $safeContent = ContentHelper::cleanHtml($request->input('content'));

        try {
            DB::beginTransaction();

            $contact = Contact::create([
                'contact_sn' => 'CT' . date('Ymd') . strtoupper(Str::random(6)),
                'fullname'   => $request->input('fullname'),
                'email'      => $request->input('email'),
                'subject'    => $request->input('subject'),
                'content'    => $safeContent,
                'ip_address' => $request->ip(),
                'status'     => 0,
            ]);

            // 配置 SMTP 並發信
            MailConfigHelper::applyFromDatabase();

            // 從設定表中獲取客服收件信箱
            $serviceEmail = DB::table('system_settings')
                            ->where('setting_key', 'service_email')
                            ->value('setting_value');

            if (filter_var($serviceEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($serviceEmail)->send(new ContactNotification($contact, $safeContent, 'notice'));
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => '訊息已送出，我們會盡快回覆您！']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("聯絡表單儲存異常: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '伺服器繁忙，請稍後再試。'], 500);
        }
    }
}
