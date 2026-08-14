<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name'); $table->string('icon'); $table->integer('services')->default(0); $table->string('status')->default('Active'); $table->timestamps();
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name'); $table->string('category'); $table->string('merchant'); $table->json('tags')->nullable(); $table->boolean('active')->default(true); $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('author'); $table->string('merchant'); $table->integer('rating'); $table->text('text'); $table->date('date'); $table->string('status'); $table->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name'); $table->string('email'); $table->string('subject'); $table->text('message'); $table->date('date'); $table->boolean('read')->default(false); $table->timestamps();
        });
        Schema::create('ads', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name'); $table->string('type'); $table->date('start'); $table->date('end'); $table->integer('impressions')->default(0); $table->integer('clicks')->default(0); $table->string('status'); $table->timestamps();
        });
        Schema::create('content_pages', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('title'); $table->string('slug'); $table->text('content'); $table->text('meta'); $table->string('status'); $table->date('updated'); $table->timestamps();
        });
        Schema::create('settlements', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('settlement_id')->nullable(); $table->string('merchant'); $table->date('date'); $table->decimal('amount', 10, 2); $table->string('method'); $table->string('status'); $table->timestamps();
        });
        Schema::create('c2_settings', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id')->default(1);
            $table->json('company_data')->nullable(); $table->json('appearance')->nullable(); $table->json('notifications')->nullable(); $table->json('security')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories'); Schema::dropIfExists('services'); Schema::dropIfExists('reviews'); Schema::dropIfExists('contact_messages'); Schema::dropIfExists('ads'); Schema::dropIfExists('content_pages'); Schema::dropIfExists('settlements'); Schema::dropIfExists('c2_settings');
    }
};
