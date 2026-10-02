<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\CallbackRequest;
use App\Models\CashEntry;
use App\Models\CashSession;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $spe = collect([
            ['Médecine générale', 'medecine-generale', 'stethoscope'],
            ['Cardiologie', 'cardiologie', 'cardiology'],
            ['Pédiatrie', 'pediatrie', 'child_care'],
            ['Ophtalmologie', 'ophtalmologie', 'visibility'],
            ['Dermatologie', 'dermatologie', 'dermatology'],
            ['Gynécologie', 'gynecologie', 'pregnant_woman'],
        ])->mapWithKeys(fn ($s) => [$s[1] => Specialty::create(['name' => $s[0], 'slug' => $s[1], 'icon' => $s[2]])]);

        $mk = fn ($slug, $name, $title, $rating, $reviews, $loc, $price = 10000) => Doctor::create([
            'specialty_id' => $spe[$slug]->id, 'name' => $name, 'title' => $title, 'rating' => $rating,
            'reviews_count' => $reviews, 'location' => $loc, 'price' => $price, 'tarif' => number_format($price, 0, ',', ' ').' FCFA', 'sector' => 'Assurances acceptées',
        ]);

        $laurent = $mk('cardiologie', 'Dr. Afi Mensah', 'Cardiologie & Médecine préventive', 4.9, 142, 'Bâtiment B - Étage 2', 15000);
        $martin = $mk('medecine-generale', 'Dr. Kodjo Agbeko', 'Médecine générale', 4.8, 210, 'Bâtiment A - Étage 1', 5000);
        $mk('medecine-generale', 'Dr. Ama Lawson', 'Médecine générale', 4.7, 96, 'Bâtiment A - Étage 1', 5000);
        $mk('pediatrie', 'Dr. Komlan Adjovi', 'Pédiatrie', 4.9, 118, 'Bâtiment C - Étage 1', 10000);
        $mk('ophtalmologie', 'Dr. Yawa Amégan', 'Ophtalmologie', 4.6, 74, 'Bâtiment B - Étage 3', 15000);
        $mk('dermatologie', 'Dr. Kossi Amouzou', 'Dermatologie', 4.8, 88, 'Bâtiment C - Étage 2', 10000);

        $sarah = User::create([
            'name' => 'Akossiwa Agbodjan', 'email' => 'demo@clinique.test', 'password' => 'password',
            'num_secu' => 'INAM 2024 004512', 'groupe_sanguin' => 'A Rh+',
            'allergies' => 'Pénicilline', 'medecin_traitant' => 'Dr. Mensah',
        ]);

        Appointment::create(['uuid' => (string) Str::uuid(), 'user_id' => $sarah->id, 'doctor_id' => $laurent->id,
            'motif' => 'Consultation de suivi', 'starts_at' => now()->addDays(3)->setTime(14, 30)]);
        Appointment::create(['uuid' => (string) Str::uuid(), 'user_id' => $sarah->id, 'doctor_id' => $martin->id,
            'motif' => 'Avis spécialisé', 'starts_at' => now()->addDay()->setTime(10, 30), 'visio' => true]);

        Storage::put('documents/bilan-lipidique.txt', "Bilan lipidique — document de démonstration.\nRemplacez par un vrai PDF stocké sur le disque privé.");
        Storage::put('documents/echographie.txt', "Compte-rendu d'échographie abdominale — document de démonstration.");

        Document::create(['user_id' => $sarah->id, 'type' => 'analyse', 'title' => 'Prise de sang & Bilan lipidique',
            'source' => 'Laboratoire Saint-Luc', 'status' => 'Normal', 'pages' => 4,
            'summary' => 'Cholestérol, Triglycérides, Glycémie à jeun', 'file_path' => 'documents/bilan-lipidique.txt',
            'issued_at' => now()->subDay()->setTime(14, 15)]);
        Document::create(['user_id' => $sarah->id, 'type' => 'imagerie', 'title' => 'Échographie abdominale',
            'source' => 'Dr. Agbeko', 'status' => 'Disponible',
            'summary' => 'Organes parenchymateux sans anomalie macroscopique décelable. Aspect normal du foie, de la vésicule biliaire et des reins.',
            'file_path' => 'documents/echographie.txt', 'issued_at' => now()->subDays(12)]);

        Prescription::create(['user_id' => $sarah->id, 'doctor_id' => $laurent->id, 'title' => 'Ordonnance traitement cardiologie',
            'valid_until' => now()->addMonths(3)->toDateString(), 'renewals_done' => 2, 'renewals_total' => 3]);

        $caissier = User::create(['name' => 'Mawuena Kpodzo', 'email' => 'caisse@clinique.test', 'password' => 'password', 'role' => 'caissier']);
        $session = CashSession::create(['cashier_id' => $caissier->id, 'opening_float' => 20000, 'opened_at' => now()->subHours(2)]);
        $day = now()->format('Ymd');
        foreach ([
            ['Aku Mensah', 'Consultation - Dr. Kodjo Agbeko', 5000, 10000, 'cash', null],
            ['Koffi Dzakpasu', 'Analyses de laboratoire', 18500, null, 'tmoney', 'TM482910357'],
            ['Ama Tettey', 'Consultation - Dr. Afi Mensah', 12000, null, 'insurance', 'INAM-2026-0456'],
            ['Ama Tettey', 'Consultation - Dr. Afi Mensah', 3000, 3000, 'cash', null],
        ] as $i => [$name, $label, $amount, $received, $method, $ref]) {
            CashEntry::create(['receipt_no' => "R-{$day}-".str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'cash_session_id' => $session->id,
                'cashier_id' => $caissier->id, 'payer_name' => $name, 'label' => $label, 'amount' => $amount, 'received' => $received, 'method' => $method, 'reference' => $ref]);
        }

        $medecin = User::create(['name' => 'Afi Mensah', 'email' => 'medecin@clinique.test', 'password' => 'password', 'role' => 'medecin']);
        $laurent->update(['user_id' => $medecin->id]);
        User::create(['name' => 'Yao Gbadoé', 'email' => 'responsable@clinique.test', 'password' => 'password', 'role' => 'responsable']);

        $komi = User::create(['name' => 'Komi Ahlonsou', 'email' => 'patient2@clinique.test', 'password' => 'password', 'groupe_sanguin' => 'O Rh+', 'num_secu' => 'INAM 2023 001873', 'medecin_traitant' => 'Dr. Mensah']);
        $edem = User::create(['name' => 'Edem Kossi', 'email' => 'patient3@clinique.test', 'password' => 'password', 'groupe_sanguin' => 'B Rh+', 'num_secu' => 'INAM 2022 009241']);
        Appointment::create(['uuid' => (string) Str::uuid(), 'user_id' => $komi->id, 'doctor_id' => $laurent->id, 'motif' => 'Première consultation', 'starts_at' => now()->setTime(9, 0), 'status' => 'done']);
        Appointment::create(['uuid' => (string) Str::uuid(), 'user_id' => $edem->id, 'doctor_id' => $laurent->id, 'motif' => 'Bilan de santé', 'starts_at' => now()->setTime(16, 0)]);
        CallbackRequest::create(['user_id' => $sarah->id, 'phone' => '90123456']);
        CallbackRequest::create(['phone' => '91223344']);

        Article::create(['title' => 'Paludisme : les bons réflexes avant la saison des pluies', 'category' => 'Prévention', 'read_minutes' => 3,
            'excerpt' => 'Moustiquaire imprégnée, eaux stagnantes, test rapide en cas de fièvre : ce qui protège vraiment votre famille.', 'published_at' => now()->subDay()]);
        Article::create(['title' => 'Vaccination des enfants : vérifiez le carnet de santé', 'category' => 'Santé publique', 'read_minutes' => 2,
            'excerpt' => 'Un rappel manqué se rattrape. Nos pédiatres vous reçoivent sur rendez-vous pour mettre le carnet à jour.', 'published_at' => now()->subDays(3)]);
        Article::create(['title' => 'Fortes chaleurs : bien s\'hydrater et reconnaître la déshydratation', 'category' => 'Bien-être', 'read_minutes' => 4,
            'excerpt' => 'Eau, repos, signes qui doivent alerter chez le nourrisson et la personne âgée : nos médecins font le point.', 'published_at' => now()->subDays(6)]);
    }
}
