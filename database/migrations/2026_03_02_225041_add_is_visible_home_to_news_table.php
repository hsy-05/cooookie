<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // 這裡就是「防呆設計」與「預設值」
            // boolean 代表真假值
            // default(false) 代表預設不顯示在首頁，避免新消息一發布就亂衝到首頁
            // after('is_visible') 是小細節，讓欄位出現在 is_visible 之後，方便資料庫閱讀
            $table->boolean('is_visible_home')->default(false)->after('is_visible');
        });
    }


    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // 這是「復原」邏輯，萬一做錯了可以刪掉欄位
            $table->dropColumn('is_visible_home');
        });
    }
};
