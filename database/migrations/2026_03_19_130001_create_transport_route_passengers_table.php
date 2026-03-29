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
        Schema::create('transport_route_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('passengers')->cascadeOnDelete();
            $table->unsignedInteger('stop_order');
            $table->timestamps();

            $table->unique(['transport_route_id', 'passenger_id'], 'trp_route_passenger_unique');
            $table->unique(['transport_route_id', 'stop_order'], 'trp_route_stop_order_unique');
            $table->index('tenant_id');
            $table->index('passenger_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_route_passengers');
    }
};