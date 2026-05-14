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
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->string('status')->default('paid')->after('type'); // pending, paid, failed, reversed
            $table->string('payment_gateway')->nullable()->after('status');
            $table->string('payment_reference')->nullable()->after('payment_gateway');
            $table->unsignedBigInteger('invoice_id')->nullable()->after('payment_reference');

            // Re-evaluating types to include topup
            // Since it's an enum in some DBs, we might need to be careful.
            // In Laravel/MySQL we can often just add to it or change to string.
        });

        // If it was an enum, we'd need to change it.
        // Let's check the previous migration content.
        // It was: $table->enum('type', ['credit', 'debit', 'reversal']);

        // I will change it to string for more flexibility or update enum.
        DB::statement('ALTER TABLE credit_transactions MODIFY COLUMN type VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credit_transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_gateway', 'payment_reference', 'invoice_id']);
        });

        DB::statement("ALTER TABLE credit_transactions MODIFY COLUMN type ENUM('credit', 'debit', 'reversal')");
    }
};
