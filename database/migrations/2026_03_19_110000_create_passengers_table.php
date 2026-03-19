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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->enum('service_type', ['ida', 'volta', 'ida_volta']);
            $table->string('name');
            $table->date('birth_date');
            $table->string('school_grade');
            $table->enum('period', ['manha', 'tarde', 'noite'])->default('tarde');
            $table->string('rg');
            $table->string('residential_zip', 8);
            $table->string('residential_street');
            $table->string('residential_number');
            $table->string('residential_complement')->nullable();
            $table->string('residential_neighborhood');
            $table->string('residential_city');
            $table->string('residential_state', 2);
            $table->string('pickup_address');
            $table->string('dropoff_address');
            $table->time('entry_time');
            $table->time('exit_time');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('guardian_id');
            $table->index(['tenant_id', 'guardian_id']);
            $table->index(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
