<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立支援的多國語言資料表
     * @return void
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->unsignedTinyInteger('lang_id')->autoIncrement()->comment('語系 ID');
            $table->string('name', 255)->comment('語系名稱');
            $table->string('alias', 255)->nullable()->comment('別名');
            $table->string('code', 10)->comment('代碼');
            $table->string('iso_code', 10)->nullable()->comment('ISO 代碼');
            $table->string('region', 255)->nullable()->comment('區域');
            $table->integer('display_order')->default(0)->comment('排序');
            $table->boolean('enabled')->default(true)->comment('啟用');
            $table->enum('display_scope', ['both', 'backend'])->default('both')->comment('顯示範圍：both=前後台, backend=僅後台');
            $table->timestamps();
        });
    }

    /**
     * 刪除多語系定義表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
