<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_code')->unique();
            $table->string('booking_type');
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time');
            $table->string('booking_status');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->dateTime('payment_time')->nullable();
            $table->unsignedBigInteger('total_price')->default(0);
            $table->string('source')->default('Website');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
