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
        Schema::table('ip_maps', function (Blueprint $table) {
            $table->dropUnique(['ip_address']);
            $table->unsignedBigInteger('shop_id')->after('ip_address');
            $table->unique(['shop_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_maps', function (Blueprint $table) {
            //
        });
    }
};
