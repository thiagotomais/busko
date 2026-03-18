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
        Schema::table('drivers', function (Blueprint $table) {
            $table->uuid('slug')->nullable()->unique()->after('id');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->uuid('slug')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
