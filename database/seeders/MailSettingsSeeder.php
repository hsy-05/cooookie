<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 建立一個父層作為「郵件設定」頁籤
        $parentId = DB::table('system_settings')->insertGetId([
            'parent_id' => 0,
            'title' => '郵件伺服器設定 (SMTP)',
            'type' => 'group',
            'is_visible' => 1,
            'display_order' => 10,
        ]);

        // 寫入具體的設定項目 (對應 Mailtrap 欄位)
        $settings = [
            ['key' => 'mail_host', 'title' => 'SMTP 伺服器 (Host)', 'value' => 'sandbox.smtp.mailtrap.io', 'desc' => '例如: smtp.gmail.com 或 Mailtrap Host'],
            ['key' => 'mail_port', 'title' => '通訊埠 (Port)', 'value' => '2525', 'desc' => '常見為 465, 587, 2525'],
            ['key' => 'mail_username', 'title' => '使用者帳號 (Username)', 'value' => '', 'desc' => 'SMTP 登入帳號'],
            ['key' => 'mail_password', 'title' => '使用者密碼 (Password)', 'value' => '', 'desc' => 'SMTP 登入密碼'],
            ['key' => 'mail_encryption', 'title' => '加密方式 (Encryption)', 'value' => 'tls', 'desc' => 'tls 或 ssl'],
            ['key' => 'mail_from_address', 'title' => '寄件者信箱', 'value' => 'noreply@mail.com', 'desc' => '客戶收信時看到的寄件者信箱'],
            ['key' => 'mail_from_name', 'title' => '寄件者名稱', 'value' => '官方網站客服中心', 'desc' => '客戶收信時看到的寄件人名稱'],
        ];

        foreach ($settings as $index => $setting) {
            DB::table('system_settings')->insert([
                'parent_id' => $parentId,
                'setting_key' => $setting['key'],
                'title' => $setting['title'],
                'description' => $setting['desc'],
                'setting_value' => $setting['value'],
                'type' => 'text',
                'is_visible' => 1,
                'display_order' => $index,
            ]);
        }
    }
}
