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
        Schema::table('alme_click_analytics', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('contact_notif_click');
            $table->unsignedBigInteger('discount_id')->nullable()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alme_click_analytics', function (Blueprint $table) {
            //
        });
    }
};
