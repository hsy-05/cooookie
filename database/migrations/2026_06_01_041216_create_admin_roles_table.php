<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 建立後台管理群組與職責表
     * @return void
     */
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('角色名稱');
            $table->string('description', 255)->nullable()->comment('描述');
            $table->longText('permissions')->nullable()->comment('權限列表');
            $table->boolean('is_systemX')->default(false)->comment('是否為系統保留(超級管理員)');
            $table->boolean('is_developerX')->default(false)->comment('是否為開發人員(上帝視角)');
            $table->timestamps();
        });
    }

    /**
     * 刪除後台角色權限表
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
