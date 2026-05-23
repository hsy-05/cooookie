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
     * 顯示聯絡我們前台頁面
     * * @return \Illuminate\View\View 聯絡我們 Blade 視圖
     */
    public function index()
    {
        // 建立頁面專屬的麵包屑路徑陣列
        $crumbs = [['text' => '關於我們', 'href' => route('about')]];

        // 呼叫父類別方法，自動處理全站共享變數
        $this->setBreadcrumbs($crumbs);

        return view('frontend.contact');
    }

    /**
     * 處理聯絡表單非同步提交與安全校驗
     * * @param ContactRequest $request 已封裝欄位驗證邏輯的表單請求物件
     * @return \Illuminate\Http\JsonResponse 回傳給前端的 JSON 狀態訊息
     */
    public function store(ContactRequest $request)
    {
        // 透過安全設定檔撈取金鑰，發送請求到 Google 伺服器驗證前端傳回的 Token
        // $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        //     'secret'   => config('services.recaptcha.secret_key'),
        //     'response' => $request->input('recaptcha_token'),
        //     'remoteip' => $request->ip(),
        // ]);

        // // 將 Google 回傳的結果解析為陣列
        // $resBody = $response->json();

        // 判斷 Google 驗證結果是否失敗，或分數低於真人門檻值（0.5）
        // if (!$resBody['success'] || $resBody['score'] < 0.5) {
        //     // 寫入系統警告日誌，記錄可疑的機器人攻擊來源
        //     Log::warning("機器人攻擊預警：IP [{$request->ip()}] 驗證分數低於門檻。");
        //     return response()->json(['success' => false, 'message' => '系統偵測到異常流量，請稍後再試。'], 403);
        // }

        // 呼叫輔助函式過濾諮詢內容，移除潛在的惡意 XSS 網頁標籤
        $safeContent = ContentHelper::cleanHtml($request->input('content'));

        try {
            // 開啟資料庫交易流程，確保入庫與發信完整一致
            DB::beginTransaction();

            // 建立聯絡我們實體紀錄，自動生成不重複的流水單號
            $contact = Contact::create([
                'contact_sn' => 'CT' . date('Ymd') . strtoupper(Str::random(6)),
                'fullname'   => $request->input('fullname'),
                'email'      => $request->input('email'),
                'subject'    => $request->input('subject'),
                'content'    => $safeContent,
                'ip_address' => $request->ip(),
                'status'     => 0,
            ]);

            // 從資料庫動態加載客戶設定的 SMTP 發信伺服器參數
            MailConfigHelper::applyFromDatabase();

            // 從全站設定資料表中獲取客服接收通知的指定信箱
            $serviceEmail = DB::table('system_settings')
                ->where('setting_key', 'service_email')
                ->value('setting_value');

            // 校驗撈出的信箱格式是否正確，確認無誤後隨即發送通知信
            if (filter_var($serviceEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($serviceEmail)->send(new ContactNotification($contact, $safeContent, 'notice'));
            }

            // 確認所有程序皆無誤後，正式寫入資料庫
            DB::commit();
            return response()->json(['success' => true, 'message' => '訊息已送出，我們會盡快回覆您！']);
        } catch (\Exception $e) {
            // 發生未知異常時撤銷所有資料庫操作，維持資料一致性
            DB::rollBack();

            // 將詳細錯誤原因寫入系統日誌，便於工程師後續追蹤排查
            Log::error("聯絡表單儲存異常: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => '伺服器繁忙，請稍後再試。'], 500);
        }
    }

    /**
     * 預覽聯絡我們郵件範本
     * 用途：直接撈取資料庫真實紀錄渲染至瀏覽器，方便精準校正通知信與回覆信的視覺外觀
     *
     * 檢查「回覆客戶」的真實樣式：
     * http://你的網址/admin/preview-contact-mail?preview_type=reply
     *
     * 檢查「新進通知管理員」的真實樣式：
     * http://你的網址/admin/preview-contact-mail?preview_type=notice
     *
     * @param \Illuminate\Http\Request $request 包含 preview_type 參數的請求物件
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function previewMail(\Illuminate\Http\Request $request)
    {
        // 撈取最新一筆聯絡單作為基礎範本資料
        $contact = Contact::orderByDesc('created_at')->first();

        // 防呆機制：若完全沒有歷史資料，則提示建立，避免後續綁定崩潰
        if (!$contact) {
            return response('資料庫內目前沒有任何聯絡單資料，請先至前台建立一筆測試資料後再進行預覽。', 404);
        }

        // 取得預覽類型：notice (通知管理員) 或 reply (回覆客戶)
        $type = $request->input('preview_type', 'reply');

        // 初始化內容變數，依據類型注入純粹的真實資料
        $content = '';

        if ($type === 'notice') {
            // 通知管理員：內容直接帶入客戶填寫的原始諮詢本文
            $content = $contact->content;
        } else {
            // 回覆客戶：嘗試抓取該諮詢單最新的管理員真實回覆紀錄
            $lastReply = $contact->replies()->orderByDesc('created_at')->first();

            if ($lastReply) {
                // 如果有真實回覆，直接帶入後台編輯器存下來的真實 HTML 內容
                $content = $lastReply->content;
            } else {
                // 防呆處理：若該諮詢單尚未被回覆過，則給予一段基礎的真實導向提示，不寫死模擬內文
                $content = "感謝您的來信，我們已收到您的諮詢，服務人員正盡速為您處理中，請稍候。";
            }
        }

        // 渲染視圖，將物件與處理完的真實內文推送至 Blade
        return view('emails.contact_mail', compact('contact', 'type', 'content'));
    }
}
