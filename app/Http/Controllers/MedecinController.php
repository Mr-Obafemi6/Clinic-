<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CallbackRequest;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    private function doctor(): Doctor
    {
        $doctor = auth()->user()->doctor;
        abort_unless($doctor, 403, 'Aucun profil médecin n\'est lié à ce compte.');

        return $doctor;
    }

    /** Un médecin n'accède qu'aux dossiers des patients qui ont (ou ont eu) rendez-vous avec lui. */
    private function doctorFor(User $patient): Doctor
    {
        $doctor = $this->doctor();
        abort_unless(
            Appointment::where('doctor_id', $doctor->id)->where('user_id', $patient->id)->exists(),
            403,
            "Ce patient n'a pas de rendez-vous avec vous."
        );

        return $doctor;
    }

    public function index()
    {
        $doctor = $this->doctor();
        $mine = fn () => Appointment::with('user')->where('doctor_id', $doctor->id);

        return view('medecin.index', [
            'doctor' => $doctor,
            'today' => $mine()->whereIn('status', ['confirmed', 'done'])->whereDate('starts_at', today())->orderBy('starts_at')->get(),
            'upcoming' => $mine()->where('status', 'confirmed')
                ->whereBetween('starts_at', [today()->addDay()->startOfDay(), today()->addDays(7)->endOfDay()])->orderBy('starts_at')->get(),
            'recent' => $mine()->whereIn('status', ['confirmed', 'done'])->where('starts_at', '<=', now()->addDay())
                ->latest('starts_at')->take(40)->get()->unique('user_id')->take(8),
            'callbacks' => CallbackRequest::with('user')->whereNull('handled_at')->latest()->get(),
        ]);
    }

    public function done(Appointment $appointment)
    {
        abort_unless($appointment->doctor_id === $this->doctor()->id, 403);

        if ($appointment->status === 'confirmed') {
            $appointment->update(['status' => 'done']);
        }

        return back()->with('status', 'Consultation marquée comme terminée.');
    }

    public function patient(User $patient)
    {
        $doctor = $this->doctorFor($patient);

        return view('medecin.patient', [
            'doctor' => $doctor,
            'patient' => $patient,
            'visits' => Appointment::where('doctor_id', $doctor->id)->where('user_id', $patient->id)->latest('starts_at')->get(),
            'documents' => $patient->documents()->latest('issued_at')->get(),
            'prescriptions' => $patient->prescriptions()->with('doctor')->latest('valid_until')->get(),
        ]);
    }

    public function storeDocument(Request $r, User $patient)
    {
        $doctor = $this->doctorFor($patient);

        $data = $r->validate([
            'title' => 'required|string|max:120',
            'type' => 'required|in:analyse,imagerie',
            'summary' => 'nullable|string|max:500',
            'status' => 'nullable|in:Normal,Disponible',
            'pages' => 'nullable|integer|min:1|max:500',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'file.required' => 'Choisissez un fichier (PDF, JPG ou PNG).',
            'file.mimes' => 'Format accepté : PDF, JPG ou PNG.',
            'file.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        // Disque privé : jamais accessible par une URL publique, seulement via RecordController::download
        $path = $r->file('file')->store('documents');

        Document::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'source' => $doctor->name,
            'status' => $data['status'] ?? 'Disponible',
            'summary' => $data['summary'] ?? null,
            'pages' => $data['pages'] ?? null,
            'file_path' => $path,
            'issued_at' => now(),
        ]);

        return back()->with('status', 'Document ajouté au dossier du patient.');
    }

    public function storePrescription(Request $r, User $patient)
    {
        $doctor = $this->doctorFor($patient);

        $data = $r->validate([
            'title' => 'required|string|max:120',
            'valid_until' => 'required|date|after:today',
            'renewals_total' => 'required|integer|min:1|max:12',
        ], ['valid_until.after' => "La date de fin de validité doit être dans le futur."]);

        Prescription::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'title' => $data['title'],
            'valid_until' => $data['valid_until'],
            'renewals_done' => 0,
            'renewals_total' => $data['renewals_total'],
        ]);

        return back()->with('status', 'Ordonnance créée. Le patient la voit dans son dossier.');
    }

    public function callbackDone(CallbackRequest $callback)
    {
        $this->doctor();
        $callback->update(['handled_at' => now(), 'handled_by' => auth()->id()]);

        return back()->with('status', 'Demande de rappel marquée comme traitée.');
    }
}
