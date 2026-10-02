<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    public const FILTERS = ['analyse' => 'Résultats labo', 'ordonnance' => 'Ordonnances', 'imagerie' => 'Imagerie'];

    public function index(Request $r)
    {
        $user = auth()->user();
        $filter = array_key_exists($r->type, self::FILTERS) ? $r->type : null;

        $documents = $user->documents()->when($filter && $filter !== 'ordonnance', fn ($q) => $q->where('type', $filter))
            ->latest('issued_at')->get();
        if ($filter === 'ordonnance') {
            $documents = collect();
        }

        $prescriptions = (! $filter || $filter === 'ordonnance')
            ? $user->prescriptions()->with('doctor')->latest('valid_until')->get()
            : collect();

        return view('record.index', [
            'user' => $user,
            'filters' => self::FILTERS,
            'filter' => $filter,
            'documents' => $documents,
            'prescriptions' => $prescriptions,
            'total' => $user->documents()->count() + $user->prescriptions()->count(),
        ]);
    }

    public function download(Document $document, Request $r)
    {
        abort_unless($document->user_id === auth()->id(), 403);
        abort_unless($document->file_path && Storage::exists($document->file_path), 404, 'Fichier introuvable.');

        return $r->boolean('inline')
            ? response()->file(Storage::path($document->file_path))
            : Storage::download($document->file_path, str($document->title)->slug().'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function sendToPharmacy(Prescription $prescription)
    {
        abort_unless($prescription->user_id === auth()->id(), 403);
        // Point d'extension : brancher ici l'envoi réel (API pharmacie, e-mail sécurisé, MSSanté…).
        $prescription->update(['sent_at' => now()]);

        return back()->with('status', 'Ordonnance envoyée à votre pharmacie.');
    }
}
