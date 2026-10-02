<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $t) {
            $t->unsignedInteger('price')->default(5000); // prix de la consultation en FCFA
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('amount');               // FCFA, fixé côté serveur
            $t->string('network');                        // TMONEY | FLOOZ
            $t->string('phone');                          // numéro Mobile Money (8 chiffres)
            $t->string('identifier')->unique();           // référence interne (envoyée à l'agrégateur)
            $t->string('tx_reference')->nullable();       // référence côté agrégateur
            $t->string('status')->default('pending');     // pending | paid | failed
            $t->dateTime('paid_at')->nullable();
            $t->timestamps();
            $t->index(['appointment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::table('doctors', fn (Blueprint $t) => $t->dropColumn('price'));
    }
};
