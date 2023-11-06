<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {

        Schema::create('product_rack_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->tinyInteger('user_liked')->default(false);
            $table->tinyInteger('crowd_fav')->default(false);
            $table->tinyInteger('pop_picks')->default(false);
            $table->tinyInteger('feat_collect')->default(false);
            $table->tinyInteger('prev_browsing')->default(false);
            $table->tinyInteger('high_convert_prods')->default(false);
            $table->tinyInteger('most_added_prods')->default(false);
            $table->tinyInteger('slow_inv')->default(false);
            $table->softDeletes();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_rack_settings');
    }
};
