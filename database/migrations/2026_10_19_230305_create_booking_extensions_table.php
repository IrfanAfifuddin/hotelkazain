<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('extension_type');
            $table->unsignedSmallInteger('duration_hours');
            $table->unsignedBigInteger('amount');
            $table->dateTime('check_out_before');
            $table->dateTime('check_out_after');
            $table->string('payment_method');
            $table->string('payment_status');
            $table->dateTime('payment_time')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extensions');
    }
};
