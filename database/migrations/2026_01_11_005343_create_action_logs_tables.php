<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            // 誰操作的？允許 null (防呆：若員工離職被刪除，紀錄還在)
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            // 做了什麼動作？(新增/修改/刪除/登入)
            $table->string('action');
            // 詳細內容 (例如：新增消息: 2026春節餅乾禮盒)
            $table->string('log_info');
            // IP 是多少
            $table->string('ip_address')->nullable();
            // 什麼時候做的 (created_at)
            $table->timestamps();

            // 建立索引 (Index)，讓之後搜尋變快
            $table->index('created_at');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_logs');
    }
};
