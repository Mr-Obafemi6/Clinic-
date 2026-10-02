<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\CashSession;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResponsableController extends Controller
{
    public const STAFF_ROLES = ['caissier' => 'Caissier', 'medecin' => 'Médecin', 'responsable' => 'Responsable'];

    public function index(Request $r)
    {
        $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        $from = $r->filled('from') ? Carbon::parse($r->from)->startOfDay() : today();
        $to = $r->filled('to') ? Carbon::parse($r->to)->endOfDay() : today()->endOfDay();
        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $entries = CashEntry::with('cashier')->whereBetween('created_at', [$from, $to])->get();
        $paid = $entries->where('status', 'paid');

        $byMethod = [];
        foreach (config('clinique.methods') as $key => $m) {
            $byMethod[$key] = (int) $paid->where('method', $key)->sum('amount');
        }

        $sessions = CashSession::with('cashier')->whereBetween('opened_at', [$from, $to])->orderBy('opened_at')->get();

        $byCashier = $paid->groupBy('cashier_id')->map(fn ($g) => [
            'name' => $g->first()->cashier->name,
            'count' => $g->count(),
            'total' => (int) $g->sum('amount'),
        ])->sortByDesc('total');

        $online = (int) Payment::where('status', 'paid')->whereBetween('paid_at', [$from, $to])->sum('amount');
        $caisse = array_sum($byMethod);

        return view('responsable.index', [
            'from' => $from, 'to' => $to,
            'methods' => config('clinique.methods'),
            'byMethod' => $byMethod, 'byCashier' => $byCashier,
            'caisse' => $caisse, 'online' => $online, 'grand' => $caisse + $online,
            'count' => $paid->count(),
            'voided' => $entries->where('status', 'voided')->values(),
            'sessions' => $sessions,
            'gapTotal' => $sessions->whereNotNull('closed_at')->sum(fn ($s) => $s->counted_cash - $s->expected_cash),
        ]);
    }

    public function team()
    {
        return view('responsable.team', [
            'staff' => User::whereIn('role', array_keys(self::STAFF_ROLES))->with('doctor.specialty')->orderBy('role')->orderBy('name')->get(),
            'roles' => self::STAFF_ROLES,
            'specialties' => Specialty::orderBy('name')->get(),
        ]);
    }

    public function storeStaff(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:'.implode(',', array_keys(self::STAFF_ROLES)),
            'specialty_id' => 'required_if:role,medecin|nullable|exists:specialties,id',
            'price' => 'required_if:role,medecin|nullable|integer|min:0|max:1000000',
            'title' => 'nullable|string|max:120',
            'location' => 'nullable|string|max:120',
        ], [
            'specialty_id.required_if' => 'Choisissez la spécialité du médecin.',
            'price.required_if' => 'Indiquez le prix de la consultation.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'role' => $data['role']]);

            if ($data['role'] === 'medecin') {
                Doctor::create([
                    'user_id' => $user->id,
                    'specialty_id' => $data['specialty_id'],
                    'name' => Str::startsWith($data['name'], 'Dr') ? $data['name'] : 'Dr. '.$data['name'],
                    'title' => $data['title'] ?? null,
                    'location' => $data['location'] ?? null,
                    'price' => $data['price'],
                    'tarif' => number_format($data['price'], 0, ',', ' ').' FCFA',
                    'sector' => 'Assurances acceptées',
                ]);
            }
        });

        return back()->with('status', 'Compte créé. Communiquez le mot de passe à la personne et demandez-lui de ne pas le partager.');
    }
}
