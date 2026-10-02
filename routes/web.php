<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\TeleconsultationController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/rendez-vous', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/rendez-vous', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/mes-rendez-vous', [AppointmentController::class, 'mine'])->name('appointments.mine');
    Route::delete('/rendez-vous/{appointment}', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/medecins/{doctor}/creneaux', [AppointmentController::class, 'slots'])->name('appointments.slots');

    Route::get('/rendez-vous/{appointment}/paiement', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/rendez-vous/{appointment}/paiement', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/paiements/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/paiements/{payment}/statut', [PaymentController::class, 'status'])->name('payments.status');

    Route::get('/dossier', [RecordController::class, 'index'])->name('record');
    Route::get('/dossier/documents/{document}', [RecordController::class, 'download'])->name('record.download');
    Route::post('/dossier/ordonnances/{prescription}/pharmacie', [RecordController::class, 'sendToPharmacy'])->name('record.pharmacy');

    Route::get('/urgences', [EmergencyController::class, 'index'])->name('emergency');
    Route::post('/urgences/rappel', [EmergencyController::class, 'callback'])->name('emergency.callback');

    Route::get('/teleconsultation/{appointment?}', [TeleconsultationController::class, 'show'])->name('tele');
});

// Espace caisse : encaisser = caissier ; consulter (reçus, journal, export) = caissier ou responsable
Route::middleware(['auth', EnsureRole::class.':caissier'])->prefix('caisse')->name('caisse.')->group(function () {
    Route::get('/', [CaisseController::class, 'index'])->name('index');
    Route::post('/ouvrir', [CaisseController::class, 'open'])->name('open');
    Route::post('/cloturer', [CaisseController::class, 'close'])->name('close');
    Route::get('/encaisser', [CaisseController::class, 'create'])->name('create');
    Route::post('/encaisser', [CaisseController::class, 'store'])->name('store');
    Route::post('/recus/{entry}/annuler', [CaisseController::class, 'void'])->name('void');
});
Route::middleware(['auth', EnsureRole::class.':caissier,responsable'])->prefix('caisse')->name('caisse.')->group(function () {
    Route::get('/recus/{entry}', [CaisseController::class, 'receipt'])->name('receipt');
    Route::get('/journal', [CaisseController::class, 'journal'])->name('journal');
    Route::get('/journal/export', [CaisseController::class, 'export'])->name('export');
});

// Espace médecin
Route::middleware(['auth', EnsureRole::class.':medecin'])->prefix('medecin')->name('medecin.')->group(function () {
    Route::get('/', [MedecinController::class, 'index'])->name('index');
    Route::post('/rendez-vous/{appointment}/termine', [MedecinController::class, 'done'])->name('done');
    Route::get('/patients/{patient}', [MedecinController::class, 'patient'])->name('patient');
    Route::post('/patients/{patient}/documents', [MedecinController::class, 'storeDocument'])->name('documents.store');
    Route::post('/patients/{patient}/ordonnances', [MedecinController::class, 'storePrescription'])->name('prescriptions.store');
    Route::post('/rappels/{callback}/traite', [MedecinController::class, 'callbackDone'])->name('callbacks.done');
});

// Espace responsable
Route::middleware(['auth', EnsureRole::class.':responsable'])->prefix('responsable')->name('responsable.')->group(function () {
    Route::get('/', [ResponsableController::class, 'index'])->name('index');
    Route::get('/equipe', [ResponsableController::class, 'team'])->name('team');
    Route::post('/equipe', [ResponsableController::class, 'storeStaff'])->name('team.store');
});
