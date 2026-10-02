<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('patient'); // patient | caissier
        });

        // Une « session » = une ouverture / clôture du tiroir-caisse
        Schema::create('cash_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $t->unsignedInteger('opening_float')->default(0); // fond de caisse
            $t->dateTime('opened_at');
            $t->dateTime('closed_at')->nullable();
            $t->unsignedInteger('expected_cash')->nullable();
            $t->unsignedInteger('counted_cash')->nullable();
            $t->string('note')->nullable();
            $t->timestamps();
        });

        // Registre de tous les encaissements faits à la caisse
        Schema::create('cash_entries', function (Blueprint $t) {
            $t->id();
            $t->string('receipt_no')->unique();
            $t->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $t->string('payer_name');
            $t->string('label');
            $t->unsignedInteger('amount');
            $t->unsignedInteger('received')->nullable(); // espèces remises par le patient
            $t->string('method');                        // cash | tmoney | flooz | card | cheque | virement | insurance
            $t->string('reference')->nullable();
            $t->string('status')->default('paid');       // paid | voided
            $t->dateTime('voided_at')->nullable();
            $t->string('void_reason')->nullable();
            $t->timestamps();
            $t->index('created_at');
            $t->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_entries');
        Schema::dropIfExists('cash_sessions');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
