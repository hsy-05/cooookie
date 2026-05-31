<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立網站核心設定參數表
     * @return void
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            // 父層 ID：0 代表它是「頁籤 (Tab)」，大於 0 代表它是該頁籤下的「設定項」
            $table->integer('parent_id')->default(0)->index();

            // 鍵名：頁籤可以不填，設定項必填 (如 image_max_size)
            $table->string('setting_key', 255)->nullable()->unique();

            // 顯示標題：例如「圖片上傳設定」或「最大上傳限制」
            $table->string('title', 255);

            // 提示文字：用於後台輸入框下方的操作說明或警告提示
            $table->text('description')->nullable();

            // 儲存實際設定值
            $table->text('setting_value')->nullable();

            // 類型：group(頁籤), text, textarea, select, number
            $table->string('type', 255)->default('text');

            // 進階配置選項：儲存 JSON 結構的參數設定（例如下拉選單的選項內容）
            $table->json('config')->nullable();

            // 檔案或圖片上傳時的目的地資料夾路徑
            $table->string('upload_dir', 255)->nullable();

            // 是否在後台介面中顯示
            $table->boolean('is_visible')->default(true);

            // 後台介面顯示時的排列順序，數字小者優先
            $table->integer('display_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * 刪除系統參數設定表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
