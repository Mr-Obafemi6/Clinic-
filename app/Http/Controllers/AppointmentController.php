<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public const MOTIFS = [
        'suivi' => ['Consultation de suivi', 'sync'],
        'premiere' => ['Première consultation', 'assignment_ind'],
        'bilan' => ['Bilan de santé', 'monitor_heart'],
        'avis' => ['Avis spécialisé', 'badge'],
    ];

    public function index(Request $r)
    {
        $query = Doctor::with('specialty')->where('available', true);

        if ($r->filled('specialite')) {
            $query->whereHas('specialty', fn ($q) => $q->where('slug', $r->specialite));
        }
        if ($r->filled('q')) {
            $term = '%'.$r->q.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhereHas('specialty', fn ($s) => $s->where('name', 'like', $term)));
        }

        $doctors = $query->get();
        $doctor = $doctors->firstWhere('id', (int) $r->medecin) ?? $doctors->first();

        $days = collect();
        if ($doctor) {
            $d = now()->startOfDay();
            while ($days->count() < 5) {
                if (! $d->isSunday()) {
                    $days->push(['date' => $d->copy(), 'count' => count($doctor->slotsFor($d))]);
                }
                $d->addDay();
            }
        }

        return view('appointments.index', [
            'specialties' => Specialty::all(),
            'doctors' => $doctors,
            'doctor' => $doctor,
            'days' => $days,
            'motifs' => self::MOTIFS,
        ]);
    }

    public function slots(Doctor $doctor, Request $r)
    {
        $date = Carbon::parse($r->validate(['date' => 'required|date|after_or_equal:today'])['date']);

        return response()->json(['slots' => $doctor->slotsFor($date)]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'motif' => 'required|in:'.implode(',', array_keys(self::MOTIFS)),
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
        ]);

        $doctor = Doctor::findOrFail($data['doctor_id']);

        if (! in_array($data['time'], $doctor->slotsFor(Carbon::parse($data['date'])), true)) {
            return back()->withErrors(['time' => "Ce créneau n'est plus disponible. Choisissez-en un autre."])->withInput();
        }

        $appointment = Appointment::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'doctor_id' => $doctor->id,
            'motif' => self::MOTIFS[$data['motif']][0],
            'starts_at' => Carbon::parse($data['date'].' '.$data['time']),
            'visio' => $r->boolean('visio'),
        ]);

        return redirect()->route('home')->with('status', 'Rendez-vous confirmé le '.$appointment->starts_at->isoFormat('dddd D MMMM [à] HH[h]mm').' avec '.$doctor->name.'.');
    }

    public function mine()
    {
        $all = auth()->user()->appointments()->with('doctor.specialty')->whereIn('status', ['confirmed', 'done'])->orderBy('starts_at')->get();

        return view('appointments.mine', [
            'upcoming' => $all->where('starts_at', '>=', now()->subHour()),
            'past' => $all->where('starts_at', '<', now()->subHour())->reverse(),
        ]);
    }

    public function cancel(Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id(), 403);
        abort_unless($appointment->status === 'confirmed', 422, 'Ce rendez-vous ne peut plus être annulé.');
        $appointment->update(['status' => 'cancelled']);

        return back()->with('status', 'Rendez-vous annulé.');
    }
}
