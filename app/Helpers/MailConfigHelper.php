<?php

namespace App\Helpers;

use Illuminate\Support\Facades\{Config, Mail, DB};

class MailConfigHelper
{
    /**
     * 從資料庫抓取設定並直接套用 (新增此方法以達成 DRY 原則)
     * 用途：一鍵完成 SMTP 設定，減少 Controller 負擔
     */
    public static function applyFromDatabase(): void
    {
        // 從 system_settings 表中取出所有設定，轉成 key => value 格式
        $settings = DB::table('system_settings')->pluck('setting_value', 'setting_key');

        // 呼叫原本的 setSmtpConfig 進行設定套用
        self::setSmtpConfig(
            $settings['mail_host'] ?? 'localhost',
            (int)($settings['mail_port'] ?? 25),
            $settings['mail_username'] ?? '',
            $settings['mail_password'] ?? '',
            $settings['mail_encryption'] ?? 'tls',
            $settings['mail_from_address'] ?? 'noreply@mail.com',
            $settings['mail_from_name'] ?? '客服中心'
        );
    }

    /**
     * 動態變更系統寄信設定 (核心邏輯)
     * @param string $host 主機位址
     * @param int $port 端口
     * @param string $username 帳號
     * @param string $password 密碼
     * @param string $encryption 加密方式
     * @param string $fromAddress 寄件者信箱
     * @param string $fromName 寄件者名稱
     */
    public static function setSmtpConfig(string $host, int $port, string $username, string $password, string $encryption, string $fromAddress, string $name): void
    {
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $name);

        $user = urlencode($username);
        $pass = urlencode($password);

        // 建構 DSN 確保連線設定直接生效，verify_peer=0 用於本地測試防報錯
        $dsn = "smtp://$user:$pass@$host:$port?verify_peer=0";

        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.url', $dsn);
        Config::set('mail.mailers.smtp.encryption', null);

        // 強制清除郵件實例快取，確保下一封信套用新設定
        Mail::purge('smtp');
    }
}
