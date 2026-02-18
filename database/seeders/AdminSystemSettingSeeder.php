<?php

namespace Database\Seeders;

use App\Models\AdminSystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 強制清空表
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AdminSystemSetting::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. 建立頁籤 (Parent) - 明確給予 setting_key 為 null
        $uploadTab = AdminSystemSetting::create([
            'parent_id'   => 0,
            'setting_key' => null, // 修正點：明確給 null
            'title'       => '上傳設定',
            'type'        => 'group',
            'display_order' => 10
        ]);

        $seoTab = AdminSystemSetting::create([
            'parent_id'   => 0,
            'setting_key' => null, // 修正點：明確給 null
            'title'       => 'SEO 設定',
            'type'        => 'group',
            'display_order' => 20
        ]);

        // 3. 建立子項 (Children)
        // 這裡 createMany 會自動繼承 parent_id 邏輯 (如果你在 Model 有設好關聯)
        $uploadTab->children()->createMany([
            [
                'setting_key'   => 'image_max_size',
                'title'         => '圖片最大限制 (KB)',
                'setting_value' => '4096',
                'type'          => 'number',
                'is_visible'    => 0,
                'display_order' => 1
            ],
            [
                'setting_key'   => 'image_extensions',
                'title'         => '允許副檔名',
                'setting_value' => 'jpg,png,webp',
                'type'          => 'text',
                'is_visible'    => 0,
                'display_order' => 2
            ]
        ]);

        $seoTab->children()->createMany([
            [
                'setting_key'   => 'site_title',
                'title'         => '網頁標題',
                'setting_value' => '我的餅乾網站',
                'type'          => 'text',
                'is_visible'    => 1,
                'display_order' => 1
            ]
        ]);

        $this->command->info('系統設定初始化成功！');
    }
}
