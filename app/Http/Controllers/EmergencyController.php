<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public const TIPS = [
        ['Chute / Entorse', 'personal_injury', "Traumatisme d'un membre ou suspicion de fracture", "Immobilisez la zone douloureuse sans forcer. Appliquez de la glace enveloppée dans un tissu pendant 15 minutes. Rendez-vous au pôle traumatologique des Urgences (accès direct sans RDV)."],
        ['Fièvre / Paludisme', 'thermostat', 'Fièvre, frissons ou maux de tête', "Mesurez la température, hydratez-vous et reposez-vous. Ici, toute fièvre doit être testée pour le paludisme (test de diagnostic rapide) : consultez le jour même, surtout pour un enfant, une femme enceinte ou si la fièvre dépasse 38,5 °C."],
        ['Douleur thoracique', 'cardiology', 'Douleur dans la poitrine', "Une douleur thoracique intense, une gêne respiratoire ou un malaise imposent d'appeler les urgences immédiatement. Ne conduisez pas vous-même."],
        ['Brûlure', 'local_fire_department', 'Brûlure légère', "Passez la zone sous l'eau tiède (15 à 20 °C) pendant 10 minutes. Ne percez pas les cloques. Consultez si la brûlure est étendue ou située sur le visage."],
    ];

    public function index()
    {
        return view('emergency', ['tips' => self::TIPS]);
    }

    public function callback(Request $r)
    {
        $data = $r->validate(['phone' => ['required', 'regex:/^[0-9 +().-]{8,20}$/']], [
            'phone.required' => 'Indiquez votre numéro de mobile.',
            'phone.regex' => 'Ce numéro ne semble pas valide.',
        ]);

        CallbackRequest::create(['user_id' => auth()->id(), 'phone' => $data['phone']]);

        return back()->with('status', 'Demande reçue : une infirmière vous rappelle en moins de 10 minutes.');
    }
}
