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
        Schema::table('passengers', function (Blueprint $table) {
            // Drop old single-string address columns
            $table->dropColumn(['pickup_address', 'dropoff_address']);
        });

        Schema::table('passengers', function (Blueprint $table) {
            // Pickup address (structured)
            $table->string('pickup_zip', 8)->after('rg');
            $table->string('pickup_street')->after('pickup_zip');
            $table->string('pickup_number')->after('pickup_street');
            $table->string('pickup_complement')->nullable()->after('pickup_number');
            $table->string('pickup_neighborhood')->after('pickup_complement');
            $table->string('pickup_city')->after('pickup_neighborhood');
            $table->string('pickup_state', 2)->after('pickup_city');

            // Dropoff address (structured)
            $table->string('dropoff_zip', 8)->after('pickup_state');
            $table->string('dropoff_street')->after('dropoff_zip');
            $table->string('dropoff_number')->after('dropoff_street');
            $table->string('dropoff_complement')->nullable()->after('dropoff_number');
            $table->string('dropoff_neighborhood')->after('dropoff_complement');
            $table->string('dropoff_city')->after('dropoff_neighborhood');
            $table->string('dropoff_state', 2)->after('dropoff_city');

            // School address
            $table->string('school_name')->after('dropoff_state');
            $table->string('school_zip', 8)->after('school_name');
            $table->string('school_street')->after('school_zip');
            $table->string('school_number')->after('school_street');
            $table->string('school_complement')->nullable()->after('school_number');
            $table->string('school_neighborhood')->after('school_complement');
            $table->string('school_city')->after('school_neighborhood');
            $table->string('school_state', 2)->after('school_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_zip', 'pickup_street', 'pickup_number', 'pickup_complement',
                'pickup_neighborhood', 'pickup_city', 'pickup_state',
                'dropoff_zip', 'dropoff_street', 'dropoff_number', 'dropoff_complement',
                'dropoff_neighborhood', 'dropoff_city', 'dropoff_state',
                'school_name', 'school_zip', 'school_street', 'school_number',
                'school_complement', 'school_neighborhood', 'school_city', 'school_state',
            ]);
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->string('pickup_address');
            $table->string('dropoff_address');
        });
    }
};
