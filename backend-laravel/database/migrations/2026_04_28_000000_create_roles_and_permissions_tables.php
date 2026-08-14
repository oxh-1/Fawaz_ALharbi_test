<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('module')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
