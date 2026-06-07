<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立前台民眾諮詢留言單據表
     * @return void
     */
    public function up(): void
    {
        Schema::create('contact', function (Blueprint $table) {
            $table->unsignedInteger('contact_id')->autoIncrement();
            $table->string('contact_sn', 20)->unique()->comment('聯絡單號');
            $table->string('fullname', 70)->comment('姓名');
            $table->string('email', 190)->comment('電子信箱');
            $table->unsignedTinyInteger('gender')->default(0)->comment('性別：0=未提供, 1=男, 2=女');
            $table->string('phone', 130)->nullable()->comment('聯絡電話');
            $table->string('subject', 120)->comment('主旨');
            $table->text('content')->comment('留言內容');
            $table->string('ip_address', 15)->comment('發文者IP');
            $table->unsignedTinyInteger('status')->default(0)->comment('狀態：0=尚未處理, 1=已讀, 2=已回覆');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * 刪除聯絡我們留言主表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};
