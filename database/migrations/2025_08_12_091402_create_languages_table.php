<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id('lang_id');                         // 主鍵：lang_id
            $table->string('name')->comment('語系名稱');   // 例如：繁體中文
            $table->string('alias')->nullable()->comment('別名'); // 例如：中文
            $table->string('code', 10)->comment('代碼');   // 例如：zh-TW
            $table->string('iso_code', 10)->nullable()->comment('ISO 代碼'); // 例如：zho
            $table->string('region')->nullable()->comment('區域'); // 例如：TW
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->boolean('enabled')->default(true)->comment('啟用');
            $table->enum('display_scope', ['both','backend'])->default('both')
                  ->comment('顯示範圍：both=前後台, backend=僅後台');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
