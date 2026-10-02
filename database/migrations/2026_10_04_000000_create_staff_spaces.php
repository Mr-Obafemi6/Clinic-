<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Compte utilisateur lié à la fiche du médecin
        Schema::table('doctors', function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->nullable()->unique();
        });

        Schema::table('documents', function (Blueprint $t) {
            $t->unsignedBigInteger('doctor_id')->nullable()->index(); // médecin qui a déposé le document
        });

        Schema::table('callback_requests', function (Blueprint $t) {
            $t->dateTime('handled_at')->nullable();
            $t->unsignedBigInteger('handled_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('callback_requests', fn (Blueprint $t) => $t->dropColumn(['handled_at', 'handled_by']));
        Schema::table('documents', fn (Blueprint $t) => $t->dropColumn('doctor_id'));
        Schema::table('doctors', fn (Blueprint $t) => $t->dropColumn('user_id'));
    }
};
