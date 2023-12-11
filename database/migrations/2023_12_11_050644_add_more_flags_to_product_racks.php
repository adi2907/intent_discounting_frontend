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
        Schema::table('product_rack_settings', function (Blueprint $table) {
            $table->tinyInteger('hps_one')->default(false)->after('store_id');
            $table->tinyInteger('hps_two')->default(false)->after('hps_one');
            $table->tinyInteger('pps_one')->default(false)->after('hps_two');
            $table->tinyInteger('pps_two')->default(false)->after('pps_one');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_rack_settings', function (Blueprint $table) {
            //
        });
    }
};
