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
        Schema::create('segment_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->mediumText('listName')->nullable();
            $table->string('lastSeen-filter')->nullable();
            $table->string('lastSeen-input')->nullable();
            $table->string('createdOn-filter')->nullable();
            $table->string('createdOn-input')->nullable();
            $table->longText('rules')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segment_rules');
    }
};
