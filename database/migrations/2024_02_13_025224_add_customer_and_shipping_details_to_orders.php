<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->longText('customer')->nullable()->after('line_items');
            $table->longText('shipping_address')->nullable()->after('customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            //
        });
    }
};
