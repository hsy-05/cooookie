<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立管理員後台動作紀錄表
     * @return void
     */
    public function up(): void
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('action', 255);
            $table->string('log_info', 255);
            $table->string('ip_address', 255)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * 刪除後台操作日誌表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
