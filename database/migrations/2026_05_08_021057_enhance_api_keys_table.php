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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->text('raw_token')->nullable()->after('token'); 
            $table->timestamp('last_used_at')->nullable()->after('expires_at');
            $table->string('last_used_ip')->nullable()->after('last_used_at');
            $table->unsignedBigInteger('request_count')->default(0)->after('last_used_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['raw_token', 'last_used_at', 'last_used_ip', 'request_count']);
        });
    }
};
