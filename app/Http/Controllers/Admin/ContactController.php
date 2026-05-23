<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\ContactRequest;
use App\Models\{Contact, ContactReply};
use Illuminate\Support\Facades\{DB, Log, Auth, Mail};
use App\Mail\ContactNotification;
use App\Helpers\{ContentHelper, MailConfigHelper, SummernoteImageHelper};

/**
 * 聯絡我們管理控制器
 * 負責前台客戶諮詢表單的讀取、已讀狀態變更、後台回覆儲存、發送通知信與刪除維護。
 */
class ContactController extends BaseAdminController
{
    // 定義權限名稱，對應後台權限控管節點
    protected $permissionName = 'contact';

    /**
     * 頁面相關配置
     * 統一管理檔案與參數，維持全站規格配置的一致性
     */
    protected $pageCfg = [
        'files' => [
            // 目前聯絡我們功能雖無圖片上傳，仍保留此結構以符合全站統一架構
        ],
    ];

    /**
     * 顯示聯絡單列表
     * 用途：載入客戶留下來的諮詢單，並提供關鍵字篩選功能
     * @param Request $request 包含搜尋關鍵字與分頁參數
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // 取得與系統配置一致的分頁筆數
        $perPage = $this->getPerPage($request);

        $contactList = Contact::when($search, function ($query) use ($search) {
            return $query->where('fullname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('contact_sn', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
        })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->view('admin.contact.index', compact('contactList', 'search'));
    }

    /**
     * 進入查看與編輯詳細內容頁面
     * 用途：顯示單筆聯絡單的詳細欄位與回覆對話紀錄
     * @param Contact $contact 透過 Route Model Binding 取得的聯絡單資料物件
     * @return \Illuminate\View\View
     */
    public function edit(Contact $contact)
    {
        return $this->renderForm($contact);
    }

    /**
     * 處理回覆儲存與郵件發送
     * 用途：儲存管理員的回覆內文，並根據勾選狀態決定是否發送電子郵件通知客戶
     * @param ContactRequest $request 驗證過後的安全請求物件
     * @param Contact $contact 聯絡單資料物件
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ContactRequest $request, Contact $contact)
    {
        return DB::transaction(function () use ($request, $contact) {
            try {
                // 清理編輯器惡意標籤，並將內文網址轉為相對標籤 [[SITE_URL]]
                $cleanReply = ContentHelper::cleanHtml($request->reply_content);
                $encodedReply = ContentHelper::encodeSiteUrl($cleanReply);

                // 精準捕捉 checkbox 的勾選狀態，確認是否需要寄信
                $shouldSendMail = $request->has('send_mail') && $request->input('send_mail') === '1';

                // 新增回覆明細紀錄
                ContactReply::create([
                    'contact_id' => $contact->contact_id,
                    'subject'    => "回覆：" . $contact->subject,
                    'content'    => $encodedReply,
                    'src_ip'     => $request->ip(),
                    'admin_id'   => Auth::id() ?: null,
                ]);

                // 更新諮詢單主表狀態為已回覆
                $contact->update(['status' => 2]);

                // 提交網頁編輯器圖片，將圖片由暫存目錄移動至正式目錄
                $editorId = $request->input('editor_id', 'default');
                SummernoteImageHelper::commitTempImages($editorId);

                // 紀錄後台管理員操作軌跡
                $contact->writeLog('回覆', "回覆客戶：{$contact->fullname} - 主旨：{$contact->subject}");

                // 處理通知信發送流程
                if ($shouldSendMail) {
                    // 動態改寫 SMTP 設定，套用後台資料庫的郵件伺服器配置
                    MailConfigHelper::applyFromDatabase();

                    // 寄出回覆信件，傳入原始乾淨內文以利收件端順利閱讀 HTML
                    Mail::to($contact->email)->send(new ContactNotification($contact, $cleanReply, 'reply'));

                    $msgTitle = '回覆內容已儲存，並已成功發送通知信！';
                } else {
                    $msgTitle = '回覆內容已儲存 (本次未發送通知信)。';
                }

                $backUrl = $request->input('back_url', route('admin.contact.index'));

                // 調用全站統一提示視窗
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
     * 用途：徹底移除聯絡單明細，並依據 Model 關聯事件同步清空對應的回覆紀錄與實體檔案
     * @param Contact $contact 透過 Route Model Binding 取得的聯絡單資料物件
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Contact $contact)
    {
        return DB::transaction(function () use ($contact) {
            try {
                $title = "客戶: {$contact->fullname} / 主旨: {$contact->subject}";

                // 執行刪除（此處會自動觸發 Model 的 static::deleting 事件，完成一條龍清理）
                $contact->delete();

                // 紀錄操作紀錄日誌
                $contact->writeLog('刪除', $title);

                return redirect()->route('admin.contact.index')->with('form_success_swal', '聯絡單及相關回覆已完整移除');
            } catch (\Exception $e) {
                Log::error("Contact Destroy Error: " . $e->getMessage());
                return back()->with('error', '刪除失敗，請洽系統管理員');
            }
        });
    }

    /**
     * 列表批次選取刪除功能
     * 用途：遍歷勾選的 ID 陣列，逐筆呼叫刪除邏輯以確保完整觸發事件
     * @param Request $request 包含勾選項目主鍵陣列 ids 的請求物件
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return back()->with('error', '請選擇要刪除的項目');

        $items = Contact::whereIn('contact_id', $ids)->get();

        foreach ($items as $item) {
            $this->destroy($item);
        }

        return redirect()->route('admin.contact.index')->with('form_success_swal', "已成功批次刪除 " . count($ids) . " 筆資料");
    }

    /**
     * 內部共用方法：準備表單渲染所需的關聯資料
     * 用途：封裝表單初始化邏輯，包含載入關聯對話紀錄、自動改寫未讀狀態、防呆清理暫存
     * @param Contact $contact 聯絡單資料物件
     * @return \Illuminate\View\View
     */
    private function renderForm(Contact $contact)
    {
        $isEdit = (bool)$contact->exists;

        if ($isEdit) {
            // 依時間升序預先載入回覆對話歷程，避免視圖產生 N+1 查詢問題
            $contact->load(['replies' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }]);

            // 自動已讀功能：若該單據原為新進單狀態(0)，進入表單即改寫為處理中(1)
            if ($contact->status === 0) {
                $contact->update([
                    'status' => 1
                ]);
            }
        }

        // 清理超過 24 小時無效的編輯器暫存圖片
        SummernoteImageHelper::cleanAbandonedImages();

        $backUrl = $this->getBackUrl('admin.contact.index');

        return $this->view('admin.contact.form', compact('contact', 'isEdit', 'backUrl'));
    }
}
