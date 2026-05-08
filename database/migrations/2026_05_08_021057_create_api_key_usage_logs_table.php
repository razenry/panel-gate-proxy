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
        Schema::create('api_key_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('api_key_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('endpoint');
            $table->string('method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_key_usage_logs');
    }
};
