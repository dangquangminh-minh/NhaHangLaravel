<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('address', 255);
            $table->string('phone', 20);
            $table->timestamps();
        });
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('name', 200)->unique();
            $table->decimal('price', 18, 2);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
        Schema::create('dish_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
        });
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->decimal('discount_amount', 18, 2);
            $table->unsignedInteger('points_cost');
            $table->unsignedInteger('quantity');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
        Schema::create('voucher_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('voucher_id')->constrained()->restrictOnDelete();
            $table->string('code', 32)->unique();
            $table->unsignedInteger('points_spent');
            $table->decimal('discount_amount', 18, 2);
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('order_status_id')->default(1)->constrained()->restrictOnDelete();
            $table->foreignId('voucher_redemption_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->uuid('checkout_token')->unique();
            $table->decimal('subtotal', 18, 2);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('total', 18, 2);
            $table->boolean('is_paid')->default(false);
            $table->enum('payment_method', ['cash', 'transfer']);
            $table->unsignedSmallInteger('table_number')->nullable();
            $table->string('delivery_address', 255)->nullable();
            $table->string('recipient_name', 100)->nullable();
            $table->string('recipient_phone', 20)->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['order_status_id', 'created_at']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('dish_id')->constrained()->restrictOnDelete();
            $table->string('dish_name', 200);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('unit_price', 18, 2);
            $table->timestamps();
            $table->unique(['order_id', 'dish_id']);
        });
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->dateTime('reserved_at');
            $table->unsignedSmallInteger('guests');
            $table->string('note', 500)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100);
            $table->string('phone', 20)->nullable();
            $table->string('subject', 200);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('message');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->unique(['dish_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'contacts', 'reservations', 'addresses', 'order_items', 'orders', 'voucher_redemptions', 'vouchers', 'order_statuses', 'dish_images', 'dishes', 'suppliers', 'categories'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
