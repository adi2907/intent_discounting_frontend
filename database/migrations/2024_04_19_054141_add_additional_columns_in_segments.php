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
        Schema::table('segment_rules', function (Blueprint $table) {
            $table->float('no_of_users')->after('listName');
            $table->string('users_measurement')->after('no_of_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('segment_rules', function (Blueprint $table) {
            //
        });
    }
};
