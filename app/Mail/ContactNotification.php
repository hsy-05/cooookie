<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Helpers\MailConfigHelper;

class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 諮詢單資料模型
     * @var Contact
     */
    public $contact;

    /**
     * 郵件本文內容（HTML 或純文字）
     * @var string
     */
    public $bodyContent;

    /**
     * 郵件類型：'notice' 代表通知管理員，'reply' 代表回覆給客戶
     * @var string
     */
    public $type;

    /**
     * 建構式：初始化郵件所需的資料
     * * @param Contact $contact 諮詢單物件
     * @param string $bodyContent 經過清理後的回覆內容
     * @param string $type 郵件用途分類
     */
    public function __construct(Contact $contact, $bodyContent, $type = 'notice')
    {
        $this->contact = $contact;
        $this->bodyContent = $bodyContent;
        $this->type = $type;
    }

    /**
     * 構建郵件內容與設定
     * * @return $this
     */
    public function build()
    {
        // 根據類型定義主旨標頭，增加辨識度
        $subjectPrefix = ($this->type === 'notice') ? "【新諮詢通知】" : "【客服回覆】";
        $subject = $subjectPrefix . $this->contact->subject;

        // 取得系統設定的寄件人資訊（通常定義在 mail_configs 表中）
        // 若您的 MailConfigHelper 已在 Controller 呼叫，這裡可作為雙重保險
        $fromEmail = config('mail.from.address');
        $fromName  = config('mail.from.name', '客服中心');

        // 指定樣板並注入變數
        return $this->from($fromEmail, $fromName)
                    ->subject($subject)
                    ->view('emails.contact_mail')
                    ->with([
                        'contact' => $this->contact,
                        'content' => $this->bodyContent,
                        'type'    => $this->type,
                    ]);
    }
}
