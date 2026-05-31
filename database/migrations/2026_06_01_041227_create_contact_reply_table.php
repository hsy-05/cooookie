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
        Schema::create('contact_reply', function (Blueprint $table) {
            $table->mediumIncrements('reply_id');

            // 關聯到 contact 表的 contact_id
            $table->unsignedMediumInteger('contact_id')->comment('關聯聯絡單ID');

            $table->string('subject', 60)->comment('回覆主旨');
            $table->text('content')->comment('回覆內容');
            $table->char('src_ip', 15)->comment('管理員IP');
            $table->unsignedSmallInteger('admin_id')->comment('管理員ID');
            // 什麼時候做的 (created_at)
            $table->timestamps();

            // 建立索引 (Index)，讓之後搜尋變快
            $table->index('created_at');
            $table->index('admin_id');

            // 建立索引加快查詢速度
            $table->index('contact_id');
        });
    }

    /**
     * 復原資料表
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_reply');
    }
};
