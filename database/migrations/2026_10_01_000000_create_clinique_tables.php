<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('num_secu')->nullable();
            $t->string('groupe_sanguin')->nullable();
            $t->string('allergies')->nullable();
            $t->string('medecin_traitant')->nullable();
        });

        Schema::create('specialties', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('icon')->default('stethoscope');
            $t->timestamps();
        });

        Schema::create('doctors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('title')->nullable();
            $t->string('photo')->default('img/doctor.jpg');
            $t->decimal('rating', 2, 1)->default(4.5);
            $t->unsignedInteger('reviews_count')->default(0);
            $t->string('sector')->default('Secteur 1');
            $t->string('location')->nullable();
            $t->string('tarif')->nullable();
            $t->boolean('available')->default(true);
            $t->timestamps();
        });

        Schema::create('appointments', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $t->string('motif');
            $t->dateTime('starts_at');
            $t->unsignedSmallInteger('duration_min')->default(30);
            $t->string('status')->default('confirmed'); // confirmed | cancelled
            $t->boolean('visio')->default(false);
            $t->timestamps();
            $t->index(['doctor_id', 'starts_at']);
        });

        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type'); // analyse | imagerie
            $t->string('title');
            $t->string('source')->nullable();
            $t->string('status')->default('Disponible');
            $t->text('summary')->nullable();
            $t->unsignedSmallInteger('pages')->nullable();
            $t->string('file_path')->nullable();
            $t->dateTime('issued_at');
            $t->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->date('valid_until');
            $t->unsignedTinyInteger('renewals_done')->default(0);
            $t->unsignedTinyInteger('renewals_total')->default(1);
            $t->string('file_path')->nullable();
            $t->dateTime('sent_at')->nullable();
            $t->timestamps();
        });

        Schema::create('articles', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('excerpt');
            $t->string('category');
            $t->unsignedTinyInteger('read_minutes')->default(3);
            $t->dateTime('published_at');
            $t->timestamps();
        });

        Schema::create('callback_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('phone');
            $t->string('status')->default('pending');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['callback_requests', 'articles', 'prescriptions', 'documents', 'appointments', 'doctors', 'specialties'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['num_secu', 'groupe_sanguin', 'allergies', 'medecin_traitant']));
    }
};
