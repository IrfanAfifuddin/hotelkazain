<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('booking_code');
            $table->string('midtrans_transaction_id')->nullable()->unique()->after('midtrans_order_id');
            $table->string('midtrans_transaction_status')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_fraud_status')->nullable()->after('midtrans_transaction_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropUnique(['midtrans_transaction_id']);
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_transaction_status',
                'midtrans_fraud_status',
            ]);
        });
    }
};
