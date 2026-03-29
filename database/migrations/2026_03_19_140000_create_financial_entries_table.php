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
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();
            $table->date('competence_month');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('status', 20)->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'passenger_id', 'competence_month'], 'fe_tenant_passenger_comp_unique');
            $table->index(['tenant_id', 'status'], 'fe_tenant_status_idx');
            $table->index(['tenant_id', 'due_date'], 'fe_tenant_due_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
