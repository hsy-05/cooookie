<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'notice' ? '新諮詢通知' : '客服回覆通知' }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Microsoft JhengHei', sans-serif; background-color: #f4f6f9; color: #333;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">

                    {{-- 郵件頁首：品牌顏色區塊 --}}
                    <tr>
                        <td align="center" style="padding: 30px 20px; background-color: #007bff; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px;">{{ config('app.name') }}</h1>
                            <p style="margin: 10px 0 0; opacity: 0.8; font-size: 14px;">專業、快速、貼心的服務體驗</p>
                        </td>
                    </tr>

                    {{-- 主要內容區 --}}
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin-top: 0; color: #007bff; font-size: 20px;">
                                {{ $type === 'notice' ? '管理員您好：系統收到一筆新的客戶諮詢' : $contact->fullname . ' 您好：' }}
                            </h2>

                            <p style="line-height: 1.8; font-size: 16px;">
                                @if($type === 'notice')
                                    以下是來自網站「聯絡我們」的諮詢詳情，請儘速處理：
                                @else
                                    感謝您的耐心等待，針對您於 <strong>{{ $contact->created_at->format('Y-m-d') }}</strong> 提出的諮詢「<strong>{{ $contact->subject }}</strong>」，我們的回覆如下：
                                @endif
                            </p>

                            {{-- 回覆內容主體區塊 --}}
                            <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #007bff; line-height: 1.6;">
                                {!! $content !!}
                            </div>

                            {{-- 原始諮詢回顧 (僅在回覆客戶時顯示) --}}
                            @if($type === 'reply')
                                <div style="margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ddd;">
                                    <h3 style="font-size: 14px; color: #888; margin-bottom: 10px;">您的原始諮詢內容：</h3>
                                    <p style="font-size: 14px; color: #666; font-style: italic;">
                                        {!! nl2br(e($contact->content)) !!}
                                    </p>
                                </div>
                            @endif

                            <p style="margin-top: 30px; font-size: 14px; color: #555;">
                                本郵件由系統自動發出，請勿直接回覆此信件。如有其他疑問，歡迎透過網站表單或致電與我們聯繫。
                            </p>
                        </td>
                    </tr>

                    {{-- 郵件頁尾：公司資訊 --}}
                    <tr>
                        <td style="padding: 20px; background-color: #f1f1f1; font-size: 12px; color: #999; text-align: center;">
                            <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }} All Rights Reserved.</p>
                            <p style="margin: 5px 0;">服務時段：週一至週五 09:00 - 18:00</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
