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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('type', ['credit', 'debit', 'reversal']);
            $table->decimal('amount', 16, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('balance_before', 16, 2);
            $table->decimal('balance_after', 16, 2);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->foreignId('reference_id')->nullable()->constrained('credit_transactions')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
