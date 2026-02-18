<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_system_settings', function (Blueprint $blueprint) {
            $blueprint->id();
            // 父層 ID：0 代表它是「頁籤 (Tab)」，大於 0 代表它是該頁籤下的「設定項」
            $blueprint->integer('parent_id')->default(0)->index();

            // 鍵名：頁籤可以不填，設定項必填 (如 image_max_size)
            $blueprint->string('setting_key')->nullable()->unique();

            // 顯示標題：例如「圖片上傳設定」或「最大上傳限制」
            $blueprint->string('title');

            $blueprint->text('setting_value')->nullable();

            // 類型：group(頁籤), text, textarea, select, number
            $blueprint->string('type')->default('text');

            $blueprint->text('range')->nullable();
            $blueprint->string('upload_dir')->nullable();
            $blueprint->boolean('is_visible')->default(1);
            $blueprint->integer('display_order')->default(0);
            $blueprint->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('admin_system_settings'); }
};
