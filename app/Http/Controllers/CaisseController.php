<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashEntry;
use App\Models\CashSession;
use App\Models\Payment;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CaisseController extends Controller
{
    private function currentSession(): ?CashSession
    {
        return CashSession::where('cashier_id', auth()->id())->whereNull('closed_at')->latest('opened_at')->first();
    }

    private function dateFrom(Request $r): Carbon
    {
        $r->validate(['date' => 'nullable|date']);

        return $r->filled('date') ? Carbon::parse($r->date)->startOfDay() : today();
    }

    /** Totaux d'une journée : caisse par mode + paiements en ligne. Les annulés sont exclus. */
    private function totalsFor(Carbon $day): array
    {
        $entries = CashEntry::whereDate('created_at', $day)->get();
        $paid = $entries->where('status', 'paid');

        $byMethod = [];
        foreach (array_keys(config('clinique.methods')) as $m) {
            $byMethod[$m] = (int) $paid->where('method', $m)->sum('amount');
        }

        $online = (int) Payment::where('status', 'paid')->whereDate('paid_at', $day)->sum('amount');
        $caisse = array_sum($byMethod);

        return [
            'by_method' => $byMethod,
            'caisse' => $caisse,
            'online' => $online,
            'grand' => $caisse + $online,
            'count' => $paid->count(),
            'voided' => $entries->where('status', 'voided')->count(),
        ];
    }

    public function index()
    {
        return view('caisse.index', [
            'session' => $this->currentSession(),
            'totals' => $this->totalsFor(today()),
            'methods' => config('clinique.methods'),
            'entries' => CashEntry::whereDate('created_at', today())->latest()->take(10)->get(),
            'last' => CashSession::where('cashier_id', auth()->id())->whereNotNull('closed_at')->latest('closed_at')->first(),
        ]);
    }

    public function open(Request $r)
    {
        if ($this->currentSession()) {
            return back()->withErrors(['session' => 'Une caisse est déjà ouverte.']);
        }

        $data = $r->validate(['opening_float' => 'required|integer|min:0|max:100000000']);
        CashSession::create(['cashier_id' => auth()->id(), 'opening_float' => $data['opening_float'], 'opened_at' => now()]);

        return redirect()->route('caisse.index')->with('status', 'Caisse ouverte. Bon service !');
    }

    public function close(Request $r)
    {
        $session = $this->currentSession();
        if (! $session) {
            return back()->withErrors(['session' => "Aucune caisse ouverte."]);
        }

        $data = $r->validate(['counted_cash' => 'required|integer|min:0|max:1000000000', 'note' => 'nullable|string|max:200']);
        $expected = $session->expectedCash();
        $session->update(['closed_at' => now(), 'counted_cash' => $data['counted_cash'], 'expected_cash' => $expected, 'note' => $data['note'] ?? null]);

        $gap = $data['counted_cash'] - $expected;

        return redirect()->route('caisse.index')->with('status', 'Caisse clôturée. '.($gap === 0 ? 'Aucun écart.' : 'Écart constaté : '.($gap > 0 ? '+' : '').Money::fcfa($gap).'.'));
    }

    public function create()
    {
        $appointments = Appointment::with(['doctor', 'user'])
            ->whereIn('status', ['confirmed', 'done'])
            ->whereBetween('starts_at', [now()->subDay()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->orderBy('starts_at')->get()
            ->filter(fn ($a) => $a->balance() > 0)->values();

        return view('caisse.create', [
            'session' => $this->currentSession(),
            'appointments' => $appointments,
            'methods' => config('clinique.methods'),
            'services' => config('clinique.services'),
        ]);
    }

    public function store(Request $r)
    {
        $session = $this->currentSession();
        if (! $session) {
            return redirect()->route('caisse.index')->withErrors(['session' => "Ouvrez la caisse avant d'encaisser."]);
        }

        $data = $r->validate([
            'source' => 'required|in:appointment,walkin',
            'appointment_id' => 'required_if:source,appointment|nullable|exists:appointments,id',
            'payer_name' => 'required_if:source,walkin|nullable|string|max:120',
            'label' => 'required_if:source,walkin|nullable|string|max:80',
            'amount' => 'required|integer|min:1|max:10000000',
            'method' => ['required', Rule::in(array_keys(config('clinique.methods')))],
            'reference' => 'nullable|string|max:120',
            'received' => 'nullable|integer|min:0|max:100000000',
        ], [
            'appointment_id.required_if' => 'Choisissez le rendez-vous à régler.',
            'payer_name.required_if' => 'Indiquez le nom du payeur.',
            'amount.required' => 'Indiquez le montant.',
        ]);

        $method = config("clinique.methods.{$data['method']}");
        if ($method['ref'] && blank($data['reference'] ?? null)) {
            return back()->withErrors(['reference' => 'Renseignez : '.$method['ref'].'.'])->withInput();
        }
        $received = $data['method'] === 'cash' && filled($data['received'] ?? null) ? (int) $data['received'] : null;
        if ($received !== null && $received < $data['amount']) {
            return back()->withErrors(['received' => 'Le montant reçu est inférieur au montant à encaisser.'])->withInput();
        }

        $appointment = null;
        if ($data['source'] === 'appointment') {
            $appointment = Appointment::with(['doctor', 'user'])->findOrFail($data['appointment_id']);
            if ($data['amount'] > $appointment->balance()) {
                return back()->withErrors(['amount' => 'Montant supérieur au solde restant ('.Money::fcfa($appointment->balance()).').'])->withInput();
            }
        }

        $entry = DB::transaction(function () use ($data, $session, $appointment, $received) {
            // Numéro de reçu : jamais réutilisé (les annulés comptent aussi)
            $n = CashEntry::whereDate('created_at', today())->lockForUpdate()->count() + 1;

            return CashEntry::create([
                'receipt_no' => 'R-'.now()->format('Ymd').'-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'cash_session_id' => $session->id,
                'cashier_id' => auth()->id(),
                'appointment_id' => $appointment?->id,
                'payer_name' => $appointment ? $appointment->user->name : $data['payer_name'],
                'label' => $appointment ? 'Consultation - '.$appointment->doctor->name : $data['label'],
                'amount' => $data['amount'],
                'received' => $received,
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
            ]);
        });

        return redirect()->route('caisse.receipt', $entry)->with('status', 'Paiement enregistré.');
    }

    public function receipt(CashEntry $entry)
    {
        return view('caisse.receipt', [
            'entry' => $entry->load('cashier', 'session', 'appointment.doctor'),
        ]);
    }

    public function void(Request $r, CashEntry $entry)
    {
        abort_if($entry->isVoided(), 422, 'Ce paiement est déjà annulé.');
        abort_unless($entry->session && $entry->session->isOpen(), 403, "Impossible d'annuler : la caisse de ce paiement est clôturée.");

        $data = $r->validate(['reason' => 'required|string|min:3|max:200'], ['reason.required' => "Indiquez le motif de l'annulation."]);
        $entry->update(['status' => 'voided', 'voided_at' => now(), 'void_reason' => $data['reason']]);

        return back()->with('status', 'Paiement annulé. Le reçu reste visible, marqué ANNULÉ.');
    }

    private function entriesFor(Carbon $date, ?string $method)
    {
        return CashEntry::with('cashier')->whereDate('created_at', $date)
            ->when($method && array_key_exists($method, config('clinique.methods')), fn ($q) => $q->where('method', $method))
            ->orderBy('created_at')->get();
    }

    public function journal(Request $r)
    {
        $date = $this->dateFrom($r);
        $method = $r->query('method');

        return view('caisse.journal', [
            'date' => $date,
            'method' => $method,
            'methods' => config('clinique.methods'),
            'entries' => $this->entriesFor($date, $method),
            'online' => $method ? collect() : Payment::with('user', 'appointment.doctor')->where('status', 'paid')->whereDate('paid_at', $date)->orderBy('paid_at')->get(),
            'totals' => $this->totalsFor($date),
            'sessions' => CashSession::with('cashier')->whereDate('opened_at', $date)->orderBy('opened_at')->get(),
        ]);
    }

    public function export(Request $r)
    {
        $date = $this->dateFrom($r);
        $entries = $this->entriesFor($date, $r->query('method'));
        $online = $r->query('method') ? collect() : Payment::with('user', 'appointment.doctor')->where('status', 'paid')->whereDate('paid_at', $date)->orderBy('paid_at')->get();

        return response()->streamDownload(function () use ($entries, $online) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 pour Excel
            $put = fn (array $row) => fputcsv($out, $row, ';', '"', '\\');
            $put(['Heure', 'Recu', 'Payeur', 'Motif', 'Mode', 'Reference', 'Montant (FCFA)', 'Statut', 'Caissier']);
            foreach ($entries as $e) {
                $put([$e->created_at->format('H:i'), $e->receipt_no, $e->payer_name, $e->label, $e->method_label, $e->reference, $e->amount, $e->isVoided() ? 'Annule' : 'Paye', $e->cashier->name]);
            }
            foreach ($online as $p) {
                $put([$p->paid_at->format('H:i'), $p->identifier, $p->user->name, 'Consultation - '.$p->appointment->doctor->name, $p->network_label.' (en ligne)', $p->tx_reference, $p->amount, 'Paye', 'En ligne']);
            }
            fclose($out);
        }, 'journal-caisse-'.$date->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
