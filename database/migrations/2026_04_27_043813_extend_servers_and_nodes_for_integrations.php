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
        Schema::table('servers', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('proxy_id')->index();
            $table->string('provisioning_type')->default('proxy')->after('status');
        });

        Schema::table('nodes', function (Blueprint $table) {
            $table->string('type')->default('proxy')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'provisioning_type']);
        });

        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
