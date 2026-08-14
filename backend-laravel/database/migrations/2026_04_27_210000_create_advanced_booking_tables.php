<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old simplistic tables to upgrade
        Schema::dropIfExists('booking_notifications');
        Schema::dropIfExists('booking_documents');
        Schema::dropIfExists('booking_customizations');
        Schema::dropIfExists('customer_favorites');
        Schema::dropIfExists('booking_status_history');
        Schema::dropIfExists('reviews'); // We drop and recreate it perfectly
        Schema::dropIfExists('bookings');

        // 2. CREATE BOOKINGS
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            
            // For backward compatibility with generic C2PlatformController string architecture
            $table->string('client')->nullable(); 
            $table->string('merchant')->nullable(); 
            $table->string('service')->nullable(); 
            
            $table->dateTime('scheduled_at')->nullable();
            $table->integer('duration_minutes')->default(60);
            
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            
            $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('payment_method', ['card', 'bank_transfer', 'cash', 'wallet'])->default('card');
            $table->string('transaction_id')->nullable();
            
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['customer', 'merchant', 'admin'])->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->dateTime('refund_date')->nullable();
            
            $table->text('customer_notes')->nullable();
            $table->json('requirements')->nullable();
            
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->unsignedBigInteger('next_booking_id')->nullable();
            
            $table->unsignedBigInteger('original_booking_id')->nullable();
            $table->dateTime('rescheduled_at')->nullable();
            
            $table->timestamps();
        });

        // 3. CREATE BOOKING_STATUS_HISTORY
        Schema::create('booking_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->enum('changed_by_role', ['customer', 'merchant', 'admin', 'system'])->default('system');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        // 4. CREATE REVIEWS
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            
            $table->string('author')->nullable(); // Legacy
            $table->string('merchant')->nullable(); // Legacy
            
            $table->tinyInteger('rating')->default(5);
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'flagged'])->default('Approved');
            $table->text('moderation_notes')->nullable();
            $table->json('photo_urls')->nullable();
            $table->text('merchant_response')->nullable();
            $table->dateTime('merchant_responded_at')->nullable();
            
            $table->boolean('verified_purchase')->default(true);
            $table->integer('is_helpful_count')->default(0);
            $table->integer('is_not_helpful_count')->default(0);
            
            // Legacy Review API fields
            $table->string('text')->nullable();
            $table->date('date')->nullable();

            $table->timestamps();
        });

        // 5. CREATE CUSTOMER_FAVORITES
        Schema::create('customer_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('merchant_id');
            $table->timestamps();
        });

        // 6. CREATE BOOKING_CUSTOMIZATIONS
        Schema::create('booking_customizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('service_id');
            $table->string('field_key', 100);
            $table->text('field_value');
            $table->timestamps();
        });

        // 7. CREATE BOOKING_DOCUMENTS
        Schema::create('booking_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->enum('document_type', ['invoice', 'receipt', 'contract', 'proof'])->default('invoice');
            $table->string('file_url', 500);
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->dateTime('generated_at');
            $table->timestamps();
        });

        // 8. CREATE BOOKING_NOTIFICATIONS
        Schema::create('booking_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('recipient_id');
            $table->enum('recipient_role', ['customer', 'merchant', 'admin']);
            $table->enum('notification_type', ['booking_confirmed', 'ical_invite', 'reminder_24h', 'reminder_1h', 'status_changed', 'cancelled', 'review_request']);
            $table->enum('channel', ['email', 'sms', 'push', 'in_app'])->default('email');
            $table->dateTime('sent_at');
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_notifications');
        Schema::dropIfExists('booking_documents');
        Schema::dropIfExists('booking_customizations');
        Schema::dropIfExists('customer_favorites');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('booking_status_history');
        Schema::dropIfExists('bookings');
    }
};
