<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 執行遷移
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            // 新增提示文字與 JSON 設定欄位
            $table->text('description')->nullable()->after('title');
            $table->json('config')->nullable()->after('range');
        });

        // 【資料遷移邏輯】將舊的 range 字串轉為 JSON 存入 config
        $settings = DB::table('system_settings')->whereNotNull('range')->get();

        foreach ($settings as $setting) {
            $rangeArray = [];
            // 解析原本的 front:前台,bg:背景 格式
            $pairs = explode(',', $setting->range);
            foreach ($pairs as $pair) {
                $parts = explode(':', $pair);
                if (count($parts) === 2) {
                    $rangeArray[trim($parts[0])] = trim($parts[1]);
                }
            }

            // 轉成 JSON 格式更新回 config 欄位
            DB::table('system_settings')
                ->where('id', $setting->id)
                ->update([
                    'config' => json_encode(['options' => $rangeArray], JSON_UNESCAPED_UNICODE)
                ]);
        }

        Schema::table('system_settings', function (Blueprint $table) {
            // 搬遷完成後，刪除舊的 range 欄位
            $table->dropColumn('range');
        });
    }

    /**
     * 回復遷移
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('range')->nullable()->after('setting_value');
            $table->dropColumn(['description', 'config']);
        });
    }
};
