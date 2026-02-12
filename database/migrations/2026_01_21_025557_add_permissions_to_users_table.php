<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            // 用來儲存個人特例權限，預設為 null (代表完全遵照角色設定)
            $table->json('permissions')->nullable()->after('role_id')->comment('個人額外權限');
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
