<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\ContactRequest;
use App\Models\{Contact, ContactReply};
use Illuminate\Support\Facades\{DB, Log, Auth, Mail};
use App\Mail\ContactNotification;
use App\Helpers\{ContentHelper, MailConfigHelper, SummernoteImageHelper};

class ContactController extends BaseAdminController
{
    // 定義權限名稱，對應 backend_permissions.php，用於自動標題與權限控管
    protected $permissionName = 'contact';

    /**
     * 頁面相關配置
     * 統一管理路徑與參數，方便未來擴充檔案上傳功能
     */
    protected $pageCfg = [
        'files' => [
            // 目前聯絡我們雖無主圖，但保留此結構以符合全站統一架構
        ],
    ];

    /**
     * 顯示聯絡單列表
     * @param Request $request 包含搜尋關鍵字與分頁參數
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 取得與系統一致的分頁筆數（具備記憶功能）
        $perPage = $this->getPerPage($request);

        $contactList = Contact::when($search, function ($query) use ($search) {
            return $query->where('fullname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->view('admin.contact.index', compact('contactList', 'search'));
    }

    /**
     * 編輯/查看聯絡單詳細內容
     * @param Contact $contact 透過 Route Model Binding 取得資料物件
     */
    public function edit(Contact $contact)
    {
        return $this->renderForm($contact);
    }

    /**
     * 處理回覆儲存與郵件發送
     * 使用 DB Transaction 確保資料紀錄與郵件發送流程的完整性
     * @param ContactRequest $request 驗證請求物件
     * @param Contact $contact 聯絡單物件
     */
    public function update(ContactRequest $request, Contact $contact)
    {
        return DB::transaction(function () use ($request, $contact) {
            try {
                // 處理編輯器內容：清理危險標籤並將網址轉為動態標籤 [[SITE_URL]]
                $cleanReply = ContentHelper::cleanHtml($request->reply_content);
                $encodedReply = ContentHelper::encodeSiteUrl($cleanReply);

                // 取得前端傳來的發信開關狀態（若無勾選則為 null，有勾選則為 '1'）
                $shouldSendMail = $request->boolean('send_mail');

                // 建立回覆紀錄
                ContactReply::create([
                    'contact_id' => $contact->contact_id,
                    'subject'    => "回覆：" . $contact->subject,
                    'content'    => $encodedReply,
                    'src_ip'     => $request->ip(),
                    'admin_id'   => Auth::id() ?: null,
                ]);

                // 更新主表狀態為已回覆 (2)
                $contact->update(['status' => 2]);

                // 提交編輯器圖片，將暫存區圖片正式轉為正式圖片，避免被自動清理機制刪除
                $editorId = $request->input('editor_id', 'default');
                SummernoteImageHelper::commitTempImages($editorId);

                // 系統操作紀錄
                $contact->writeLog('回覆', "回覆客戶：{$contact->fullname} - {$contact->subject}");

                // 判斷是否需要同步發送 Email 通知
                if ($shouldSendMail) {
                    // 套用後台設定的 SMTP 伺服器配置（避免使用預設環境變數）
                    MailConfigHelper::applyFromDatabase();

                    // 執行發信動作：傳入諮詢物件與乾淨的 HTML 內容
                    Mail::to($contact->email)->send(new ContactNotification($contact, $cleanReply, 'reply'));

                    $msgTitle = '回覆內容已儲存，並已成功發送通知信！';
                } else {
                    $msgTitle = '回覆內容已儲存 (本次未發送通知信)。';
                }

                // 定義操作成功後的返回路徑
                $backUrl = $request->input('back_url', route('admin.contact.index'));

                // 呼叫統一的訊息顯示介面
                $this->showMsg(0, $msgTitle, [
                    ['text' => '返回列表', 'href' => $backUrl],
                    ['text' => '留在本頁', 'href' => route('admin.contact.edit', $contact->contact_id)],
                ]);

                return redirect()->back();
            } catch (\Exception $e) {
                Log::error("Contact Update (Reply) Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', '發送失敗：' . $e->getMessage());
            }
        });
    }
    /**
     * 刪除單筆聯絡單
     * @param Contact $contact 透過 Route Model Binding 取得的物件
     */
    public function destroy(Contact $contact)
    {
        return DB::transaction(function () use ($contact) {
            try {
                // 暫存標題以便紀錄日誌
                $title = "客戶: {$contact->fullname} / 主旨: {$contact->subject}";

                // 執行刪除：此時會觸發 Contact Model 的 boot deleting 事件
                // 自動清理聯絡單回覆內容、回覆中的圖片檔案、以及回覆紀錄本身
                $contact->delete();

                // 紀錄系統日誌
                $contact->writeLog('刪除', $title);

                // 如果是 AJAX 請求則回傳 JSON，否則導回列表（依據專案習慣調整）
                return redirect()->route('admin.contact.index')->with('form_success_swal', '聯絡單及相關回覆已完整移除');
            } catch (\Exception $e) {
                Log::error("Contact Destroy Error: " . $e->getMessage());
                return back()->with('error', '刪除失敗，請洽系統管理員');
            }
        });
    }

    /**
     * 列表批次刪除
     * @param Request $request
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', '請選擇要刪除的項目');

        $items = Contact::whereIn('contact_id', $ids)->get();

        foreach ($items as $item) {
            // 透過迴圈刪除以觸發 Model 事件（清理圖片與回覆）
            $this->destroy($item);
        }

        return redirect()->route('admin.contact.index')->with('form_success_swal', "已成功批次刪除 " . count($ids) . " 筆資料");
    }

    /* --- 內部輔助方法 --- */

    /**
     * 渲染表單頁面
     * @param Contact $contact 聯絡單物件
     */
    private function renderForm(Contact $contact)
    {
        // 判斷是否為已存在的資料（聯絡我們通常只有 Edit 模式）
        $isEdit = (bool)$contact->exists;

        // 載入回覆紀錄
        if ($isEdit) {
            $contact->load(['replies' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }]);

            // 如果狀態為「尚未處理 (0)」，進入此頁面代表已讀，更新狀態
            if ($contact->status === 0) {
                $contact->update([
                    'status' => 1
                ]);
            }
        }

        // 清理先前未存檔即關閉頁面產生的垃圾圖片暫存
        SummernoteImageHelper::cleanAbandonedImages();

        // 預設返回路徑
        $backUrl = $this->getBackUrl('admin.contact.index');

        return $this->view('admin.contact.form', compact('contact', 'isEdit', 'backUrl'));
    }
}
