<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ip_maps', function (Blueprint $table) {
            // Drop the old unique constraint on ip_address
            $table->dropUnique(['ip_address']);

            // Add new composite unique constraints
            $table->unique(['shop_id', 'ip_address']);
            $table->unique(['shop_id', 'ipv6_address']);
        });
    }

    public function down(): void
    {
        Schema::table('ip_maps', function (Blueprint $table) {
            // Drop the new composite unique constraints
            $table->dropUnique(['shop_id', 'ip_address']);
            $table->dropUnique(['shop_id', 'ipv6_address']);

            // Re-add the old unique constraint on ip_address
            $table->unique('ip_address');
        });
    }
};