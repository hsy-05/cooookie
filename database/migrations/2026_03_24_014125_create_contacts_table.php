<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 執行資料表建立
     */
    public function up(): void
    {
        Schema::create('contact', function (Blueprint $table) {
            // 使用 mediumIncrements 建立 MEDIUMINT 的主鍵
            $table->mediumIncrements('contact_id');

            // 設定各欄位長度與型態
            $table->string('contact_sn', 20)->unique()->comment('聯絡單號');
            $table->string('fullname', 70)->comment('姓名');
            $table->string('email', 190)->comment('電子信箱');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性別：0=未提供, 1=男, 2=女');
            $table->string('phone', 130)->nullable()->comment('聯絡電話');
            $table->string('subject', 120)->comment('主旨');
            $table->text('content')->comment('留言內容');
            $table->string('ip_address', 15)->comment('發文者IP');

            // 狀態與時間紀錄
            $table->unsignedTinyInteger('status')->default(0)->comment('狀態：0=尚未處理, 1=已讀, 2=已回覆');
            // 什麼時候做的 (created_at)
            $table->timestamps();

            // 建立索引 (Index)，讓之後搜尋變快
            $table->index('created_at');
        });
    }

    /**
     * 復原資料表
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};
