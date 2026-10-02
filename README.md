# Clinique Saint-Luc — portail patient (Laravel)

Ce dossier contient **uniquement le code de l'application** (migrations, modèles, contrôleurs, routes, vues Blade, CSS, JS).
Il se pose par-dessus une installation Laravel neuve.

## Installation

```bash
composer create-project laravel/laravel clinique-saint-luc
cd clinique-saint-luc
```

1. Copiez le contenu de ce dossier **par-dessus** le projet (acceptez de remplacer `routes/web.php` et `app/Models/User.php`, `database/seeders/DatabaseSeeder.php`).
2. Reprenez les lignes de `.env.additions` dans votre `.env`.
3. Lancez :

```bash
php artisan migrate:fresh --seed
php artisan serve
```

Ouvrez http://127.0.0.1:8000 et connectez-vous avec :

- e-mail : `demo@clinique.test`
- mot de passe : `password`

Aucune étape `npm` : le CSS et le JavaScript sont de simples fichiers dans `public/`.
La base SQLite de Laravel suffit ; pour MySQL, changez les variables `DB_*` du `.env`.

## Ce qui est fait

| Écran | Route | Fonctionnalités |
|---|---|---|
| Connexion / inscription | `/connexion`, `/inscription` | Authentification Laravel (session) |
| Accueil | `/` | Prochain RDV (J-x), services, pôles, actualités, humeur du jour |
| Prise de RDV | `/rendez-vous` | Recherche, filtre par spécialité, choix du médecin, motif, 5 prochains jours, créneaux chargés en AJAX, anti-double réservation, option visio |
| Mes RDV | `/mes-rendez-vous` | Liste, annulation, reprogrammation |
| Dossier médical | `/dossier` | Carte patient, filtres, aperçu / téléchargement protégés par propriétaire, ordonnances avec renouvellements |
| Urgences | `/urgences` | SAMU 15 / 112, GPS, conseils par symptôme, demande de rappel soignant (enregistrée en base) |
| Téléconsultation | `/teleconsultation` | Salle Jitsi liée au RDV visio |

## À savoir avant une mise en production

- **Visio** : l'instance publique `meet.jit.si` limite l'intégration dans un site tiers. Pour un usage réel, utilisez 8x8 JaaS ou un Jitsi auto-hébergé et renseignez `JITSI_DOMAIN` dans `.env`.
- **Documents** : les fichiers de démonstration sont des `.txt` dans `storage/app/private/documents`. Remplacez-les par de vrais PDF : le téléchargement passe par un contrôle d'accès (`RecordController::download`).
- **Envoi à la pharmacie** : l'action enregistre la date d'envoi. Le branchement réel (API pharmacie, messagerie sécurisée) se fait dans `RecordController::sendToPharmacy`.
- **Rappel soignant** : les demandes sont stockées dans `callback_requests`. Il reste à créer l'écran ou la notification côté équipe soignante.
- **Conformité santé** : le bandeau « HDS / AES-256 » des maquettes est du texte. La certification HDS dépend de l'hébergeur et du chiffrement mis en place, pas du code seul.
- **Données de démo** : les 6 médecins partagent la même photo (`public/img/doctor.jpg`). Changez le champ `photo` de chaque médecin.
