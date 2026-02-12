<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            // 關聯角色 ID (nullable 是為了防止舊資料報錯，之後應設為必填)
            $table->foreignId('role_id')->nullable()->after('id')->constrained('admin_roles')->nullOnDelete();
            $table->string('avatar_url')->nullable()->after('name')->comment('大頭貼');
            $table->boolean('is_active')->default(1)->after('password')->comment('帳號狀態 1:啟用 0:停用');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'avatar_url', 'is_active']);
        });
    }
};
