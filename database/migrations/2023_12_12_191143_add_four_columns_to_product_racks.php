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
            $table->tinyInteger('usersAlsoLiked')->default(false)->after('store_id');
            $table->tinyInteger('featuredCollection')->default(false)->after('usersAlsoLiked');
            $table->tinyInteger('pickUpWhereYouLeftOff')->default(false)->after('featuredCollection');
            $table->tinyInteger('crowdFavorites')->default(false)->after('pickUpWhereYouLeftOff');
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
