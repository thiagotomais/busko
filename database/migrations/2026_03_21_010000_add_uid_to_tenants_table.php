<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->uuid('uid')->nullable()->unique()->after('id');
        });

        DB::table('tenants')
            ->whereNull('uid')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($tenant): void {
                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update(['uid' => (string) Str::uuid()]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['uid']);
            $table->dropColumn('uid');
        });
    }
};
