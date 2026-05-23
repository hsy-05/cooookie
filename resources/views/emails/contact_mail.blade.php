<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'notice' ? '新諮詢通知' : '客服回覆通知' }}</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Noto Sans TC', 'PingFang TC', 'Microsoft JhengHei', sans-serif; background-color: #eee8e0; color: #2c2420; line-height: 1.8; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600"
                    style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(44, 36, 32, 0.08);">

                    {{-- 郵件頁首：品牌木質色區塊 --}}
                    <tr>
                        <td align="center" style="padding: 40px 20px; background-color: #8c6a4b; color: #ffffff;">
                            <h1
                                style="margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 0.05em; font-family: 'Rubik', 'Noto Sans TC', sans-serif;">
                                {{ config('app.name') }}</h1>
                            <div
                                style="width: 40px; height: 1px; background-color: #ffffff; margin: 15px auto; opacity: 0.5;">
                            </div>
                            <p
                                style="margin: 0; opacity: 0.85; font-size: 14px; letter-spacing: 0.1em; font-weight: 400;">
                                專業、快速、貼心的服務體驗</p>
                        </td>
                    </tr>

                    {{-- 主要內容區 --}}
                    <tr>
                        <td style="padding: 45px 40px; background-color: #ffffff;">
                            <h2
                                style="margin-top: 0; margin-bottom: 24px; color: #8c6a4b; font-size: 20px; font-weight: 600; letter-spacing: 0.03em;">
                                {{ $type === 'notice' ? '管理員您好：系統收到一筆新的客戶諮詢' : '親愛的 ' . $contact->fullname . ' 您好：' }}
                            </h2>

                            <p style="margin-bottom: 20px; font-size: 16px; color: #2c2420; line-height: 1.8;">
                                @if ($type === 'notice')
                                    親愛的 管理員 您好, 產生新的諮詢紀錄了
                                    為了保護個人資料安全, 請登入管理後台查詢該筆諮詢紀錄
                                @else
                                    您的諮詢，管理者已完成回覆。<br>回覆內容如下：
                                @endif
                            </p>

                            {{--
                              新進諮詢明細表 (僅在通知管理員時呈現)
                              採用您提供的標準簡潔明細表格設計，並以變數動態對接
                            --}}
                            @if ($type === 'notice')
                                <table
                                    style="border-collapse: collapse; border: 1px solid #dddddd; margin: 20px 0; width: 100%; font-size: 15px;">
                                    <tbody>
                                        <tr>
                                            <td
                                                style="width: 120px; text-align: right; padding: 12px 15px; vertical-align: top; background-color: #faf8f5; font-weight: bold; border-bottom: 1px solid #eeeeee;">
                                                諮詢編號：</td>
                                            <td
                                                style="padding: 12px 15px; vertical-align: top; border-bottom: 1px solid #eeeeee; color: #5d4037;">
                                                {{ $contact->contact_sn ?? '無' }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td style="text-align: right; padding: 12px 15px; vertical-align: top; background-color: #faf8f5; font-weight: bold; border-bottom: 1px solid #eeeeee;">客戶姓名：</td>
                                            <td style="padding: 12px 15px; vertical-align: top; border-bottom: 1px solid #eeeeee;">{{ $contact->fullname }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right; padding: 12px 15px; vertical-align: top; background-color: #faf8f5; font-weight: bold; border-bottom: 1px solid #eeeeee;">電子信箱：</td>
                                            <td style="padding: 12px 15px; vertical-align: top; border-bottom: 1px solid #eeeeee;">{{ $contact->email }}</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right; padding: 12px 15px; vertical-align: top; background-color: #faf8f5; font-weight: bold; border-bottom: 1px solid #eeeeee;">諮詢主旨：</td>
                                            <td style="padding: 12px 15px; vertical-align: top; border-bottom: 1px solid #eeeeee; font-weight: bold;">{{ $contact->subject }}</td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            @endif

                            {{--
                              核心內文顯示區塊
                              白色背景防衝突，木質外框收尾。此處即為前台內文或後台真實回覆內文
                            --}}
                            @if ($type === 'reply')
                                <div class="mail-content-body"
                                    style="margin: 25px 0; padding: 24px; background-color: #ffffff; border: 1px solid #8c6a4b; border-radius: 8px; font-size: 16px; color: #2c2420; line-height: 1.8;">
                                    <style>
                                        .mail-content-body img {
                                            max-width: 100% !important;
                                            height: auto !important;
                                        }
                                    </style>
                                    {!! $content !!}
                                </div>
                            @endif

                            {{-- 頁尾溫馨提醒與宣告 --}}
                            <p style="margin-top: 30px; margin-bottom: 0; font-size: 15px; color: #2c2420;">
                                @if ($type === 'reply')
                                    若您有任何的疑問歡迎隨時與我們聯絡。
                                @endif
                            </p>

                            <p
                                style="margin-top: 35px; margin-bottom: 0; font-size: 13px; color: #d4a373; line-height: 1.6;">
                                ※ 本郵件由系統自動發出，請勿直接回覆此信件。如有其他疑問，歡迎透過網站表單或致電與我們聯繫。
                            </p>
                        </td>
                    </tr>

                    {{-- 郵件頁尾：暖深褐公司資訊區 --}}
                    <tr>
                        <td
                            style="padding: 30px 20px; background-color: #2c2420; font-size: 12px; color: #eee8e0; text-align: center; line-height: 2.0; letter-spacing: 0.05em;">
                            <p style="margin: 0; opacity: 0.9;">&copy; {{ date('Y') }} {{ config('app.name') }} All
                                Rights Reserved.</p>
                            <p style="margin: 5px 0 0; opacity: 0.7;">服務時段：週一至週五 09:00 - 18:00</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
