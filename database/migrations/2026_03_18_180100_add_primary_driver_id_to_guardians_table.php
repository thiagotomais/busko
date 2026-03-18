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
        Schema::table('guardians', function (Blueprint $table) {
            $table->foreignId('primary_driver_id')->nullable()->after('user_id')->constrained('drivers')->nullOnDelete();
            $table->index(['tenant_id', 'primary_driver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_driver_id');
        });
    }
};