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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('bank_code')->nullable()->after('slug')->comment('Código do banco (ex: 001 para Banco do Brasil)');
            $table->string('bank_branch')->nullable()->after('bank_code')->comment('Agência bancária');
            $table->string('bank_account')->nullable()->after('bank_branch')->comment('Número da conta');
            $table->enum('bank_account_type', ['corrente', 'poupanca'])->nullable()->after('bank_account')->comment('Tipo de conta');
            $table->string('pix_key')->nullable()->after('bank_account_type')->comment('Chave Pix (CPF, email, telefone, ou chave aleatória)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['bank_code', 'bank_branch', 'bank_account', 'bank_account_type', 'pix_key']);
        });
    }
};
