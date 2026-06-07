<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立後台人員登入帳號表
     * @return void
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->longText('preferences')->nullable()->comment('個人化設定');
            $table->longText('permissions')->nullable()->comment('個人額外權限');
            $table->string('name', 255);
            $table->string('avatar_url', 255)->nullable()->comment('大頭貼');
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->boolean('is_active')->default(true)->comment('帳號狀態 1:啟用 0:停用');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * 刪除後台管理員表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
