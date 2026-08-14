<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name');
            $table->decimal('monthly_price', 8, 2);
            $table->decimal('annual_price', 8, 2);
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
