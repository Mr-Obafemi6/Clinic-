<?php

// Informations de la clinique : remplacez par les vraies coordonnées (ou via le fichier .env).
return [
    'name' => 'Clinique Saint-Luc',
    'city' => 'Lomé',
    'phone' => env('CLINIQUE_PHONE', '+228 90 00 00 00'),
    'phone_tel' => preg_replace('/[^\d+]/', '', env('CLINIQUE_PHONE', '+228 90 00 00 00')),
    'address' => env('CLINIQUE_ADDRESS', 'Quartier Tokoin, Lomé, Togo'),

    // Numéros publics vérifiés : police 117, pompiers 118.
    'police' => '117',
    'pompiers' => '118',
    // Numéro d'urgence médicale (SAMU / ambulance) : à renseigner une fois confirmé auprès du Ministère de la Santé.
    'samu' => env('SAMU_NUMBER'),

    // Modes de paiement acceptés à la caisse. 'ref' = libellé de la référence à saisir (null = aucune).
    'methods' => [
        'cash' => ['label' => 'Espèces', 'icon' => 'payments', 'ref' => null],
        'tmoney' => ['label' => 'T-Money', 'icon' => 'smartphone', 'ref' => 'N° de transaction T-Money'],
        'flooz' => ['label' => 'Flooz', 'icon' => 'smartphone', 'ref' => 'N° de transaction Flooz'],
        'card' => ['label' => 'Carte bancaire', 'icon' => 'credit_card', 'ref' => "N° d'autorisation (reçu du TPE)"],
        'cheque' => ['label' => 'Chèque', 'icon' => 'receipt_long', 'ref' => 'N° de chèque et banque'],
        'virement' => ['label' => 'Virement', 'icon' => 'account_balance', 'ref' => 'Référence du virement'],
        'insurance' => ['label' => 'Assurance / INAM', 'icon' => 'health_and_safety', 'ref' => 'N° de prise en charge'],
    ],

    // Motifs proposés pour un passage sans rendez-vous.
    'services' => ['Consultation', 'Analyses de laboratoire', 'Imagerie / échographie', 'Soins infirmiers', 'Pharmacie', 'Hospitalisation / acompte', 'Autre'],

    // Paiement mobile : 'simulated' (démonstration, aucun débit) ou 'paygate' (T-Money / Flooz via PayGate Global).
    'payments' => [
        'driver' => env('PAYMENT_DRIVER', 'simulated'),
        'paygate_token' => env('PAYGATE_TOKEN'),
        'paygate_base' => env('PAYGATE_BASE_URL', 'https://paygateglobal.com'),
    ],

    // Serveur de visio (voir README : meet.jit.si limite l'intégration, préférez 8x8 JaaS ou un Jitsi auto-hébergé)
    'jitsi_domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
];
