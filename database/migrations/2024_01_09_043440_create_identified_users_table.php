<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('identified_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('shop_id' );
            $table->text('name');
            $table->string('last_visited');
            $table->string('email');
            $table->integer('serial_number');
            $table->string('phone');
            $table->integer('visited');
            $table->integer('added_to_cart');
            $table->integer('purchased');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identified_users');
    }
};
