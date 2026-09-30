<?php

namespace Database\Seeders;

use App\Models\{Product, Category, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = User::where('role', 'vendeur')->first();
        $vendorId = $vendor?->id;

        $products = [
            // Mobilité
            ['cat' => 'fauteuils-roulants', 'nom' => 'Fauteuil roulant manuel pliable Invacare Action 3', 'marque' => 'Invacare', 'desc' => 'Fauteuil roulant manuel léger et pliable, assise réglable, idéal pour un usage quotidien à domicile ou en extérieur.', 'vente' => 420000, 'loc_j' => 3000, 'loc_s' => 18000, 'loc_m' => 60000, 'caution' => 100000, 'poids' => 14.5, 'dim' => '110x65x90 cm', 'install' => false, 'certif' => true],
            ['cat' => 'deambulateurs-et-cannes', 'nom' => 'Déambulateur pliant à roulettes Invacare', 'marque' => 'Invacare', 'desc' => 'Déambulateur 2 roues avec freins et panier de rangement, hauteur réglable pour un meilleur maintien.', 'vente' => 65000, 'loc_j' => 1000, 'loc_s' => 5000, 'loc_m' => 15000, 'caution' => 20000, 'poids' => 4.2, 'dim' => '60x55x90 cm', 'install' => false, 'certif' => false],
            ['cat' => 'scooters-medicalises', 'nom' => 'Scooter électrique médicalisé 4 roues', 'marque' => 'Invacare', 'desc' => 'Scooter de mobilité 4 roues, autonomie 25 km, idéal pour les déplacements extérieurs des personnes à mobilité réduite.', 'vente' => 1250000, 'loc_j' => 8000, 'loc_s' => 45000, 'loc_m' => 150000, 'caution' => 300000, 'poids' => 65, 'dim' => '130x60x110 cm', 'install' => true, 'certif' => true],
            ['cat' => 'leve-personnes', 'nom' => 'Lève-personne mobile électrique Invacare Birdie', 'marque' => 'Invacare', 'desc' => 'Lève-personne électrique compact pour le transfert en toute sécurité des patients à mobilité très réduite.', 'vente' => 980000, 'loc_j' => 6000, 'loc_s' => 35000, 'loc_m' => 120000, 'caution' => 250000, 'poids' => 38, 'dim' => '110x65x140 cm', 'install' => true, 'certif' => true],

            // Lits et mobilier médical
            ['cat' => 'lits-medicalises', 'nom' => 'Lit médicalisé électrique 3 fonctions', 'marque' => 'Invacare', 'desc' => 'Lit médicalisé à hauteur variable avec relève-buste et relève-jambes électriques, barrières de sécurité incluses.', 'vente' => 750000, 'loc_j' => 5000, 'loc_s' => 28000, 'loc_m' => 95000, 'caution' => 200000, 'poids' => 85, 'dim' => '200x100x60 cm', 'install' => true, 'certif' => true],
            ['cat' => 'matelas-anti-escarres', 'nom' => 'Matelas anti-escarres à air motorisé', 'marque' => 'Invacare', 'desc' => 'Matelas thérapeutique à cellules d\'air alternées pour la prévention et le traitement des escarres.', 'vente' => 185000, 'loc_j' => 2000, 'loc_s' => 12000, 'loc_m' => 40000, 'caution' => 50000, 'poids' => 9, 'dim' => '200x90x18 cm', 'install' => false, 'certif' => true],
            ['cat' => 'fauteuils-de-repos', 'nom' => 'Fauteuil releveur électrique de repos', 'marque' => 'Invacare', 'desc' => 'Fauteuil releveur avec assistance électrique au lever, revêtement simili-cuir facile d\'entretien.', 'vente' => 320000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 42, 'dim' => '80x90x110 cm', 'install' => false, 'certif' => false],

            // Respiration et oxygénothérapie
            ['cat' => 'concentrateurs-doxygene', 'nom' => 'Concentrateur d\'oxygène 5L Philips Respironics EverFlo', 'marque' => 'Philips Respironics', 'desc' => 'Concentrateur d\'oxygène stationnaire, débit jusqu\'à 5L/min, fonctionnement silencieux pour usage domicile.', 'vente' => 650000, 'loc_j' => 4000, 'loc_s' => 22000, 'loc_m' => 75000, 'caution' => 150000, 'poids' => 14, 'dim' => '38x33x58 cm', 'install' => true, 'certif' => true],
            ['cat' => 'appareils-cpapbipap', 'nom' => 'Appareil CPAP auto-piloté Philips DreamStation', 'marque' => 'Philips Respironics', 'desc' => 'Appareil de traitement de l\'apnée du sommeil avec ajustement automatique de la pression et humidificateur intégré.', 'vente' => 480000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 1.3, 'dim' => '15x15x22 cm', 'install' => false, 'certif' => true],
            ['cat' => 'aspirateurs-de-mucosites', 'nom' => 'Aspirateur de mucosités portable', 'marque' => 'Invacare', 'desc' => 'Aspirateur médical portable rechargeable pour l\'aspiration des sécrétions, batterie longue durée.', 'vente' => 195000, 'loc_j' => 1500, 'loc_s' => 8000, 'loc_m' => 28000, 'caution' => 40000, 'poids' => 2.8, 'dim' => '25x20x30 cm', 'install' => false, 'certif' => true],

            // Diagnostic et mesure
            ['cat' => 'tensiometres', 'nom' => 'Tensiomètre électronique bras Omron M3', 'marque' => 'Omron', 'desc' => 'Tensiomètre automatique au bras avec détection d\'arythmie et mémoire pour deux utilisateurs.', 'vente' => 32000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.4, 'dim' => '14x10x6 cm', 'install' => false, 'certif' => false, 'couleurs' => ['#ffffff', '#1e3a7a']],
            ['cat' => 'oxymetres', 'nom' => 'Oxymètre de pouls Beurer PO40', 'marque' => 'Beurer', 'desc' => 'Oxymètre de pouls digital pour mesurer la saturation en oxygène et la fréquence cardiaque.', 'vente' => 18000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.05, 'dim' => '6x3x3 cm', 'install' => false, 'certif' => false],
            ['cat' => 'glucometres', 'nom' => 'Glucomètre Beurer GL50 avec bandelettes', 'marque' => 'Beurer', 'desc' => 'Lecteur de glycémie avec 10 bandelettes et 10 lancettes incluses, résultat en 5 secondes.', 'vente' => 25000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.1, 'dim' => '9x5x2 cm', 'install' => false, 'certif' => false],
            ['cat' => 'balances-medicales', 'nom' => 'Balance médicale pèse-personne électronique', 'marque' => 'Microlife', 'desc' => 'Balance médicale de précision avec grand écran LCD, capacité 200 kg.', 'vente' => 45000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 3.5, 'dim' => '32x32x5 cm', 'install' => false, 'certif' => false],

            // Imagerie médicale
            ['cat' => 'echographes', 'nom' => 'Échographe portable Mindray DP-10', 'marque' => 'Mindray', 'desc' => 'Échographe portable polyvalent pour cabinet médical, écran haute résolution et sondes interchangeables.', 'vente' => 8500000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 6.5, 'dim' => '40x35x15 cm', 'install' => true, 'certif' => true],
            ['cat' => 'radiographie', 'nom' => 'Appareil de radiographie mobile', 'marque' => 'GE Healthcare', 'desc' => 'Générateur de radiographie mobile pour clinique, tension réglable, châssis sur roulettes.', 'vente' => 15000000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 180, 'dim' => '80x70x160 cm', 'install' => true, 'certif' => true],

            // Bloc opératoire
            ['cat' => 'moniteurs-multiparametriques', 'nom' => 'Moniteur multiparamétrique patient Mindray uMEC12', 'marque' => 'Mindray', 'desc' => 'Moniteur de surveillance ECG, SpO2, tension, écran couleur 12 pouces, autonomie sur batterie.', 'vente' => 1450000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 5.5, 'dim' => '32x28x15 cm', 'install' => true, 'certif' => true],
            ['cat' => 'defibrillateurs', 'nom' => 'Défibrillateur semi-automatique Philips HeartStart', 'marque' => 'Philips', 'desc' => 'Défibrillateur externe automatisé avec guidage vocal, idéal pour les urgences cardiaques.', 'vente' => 1100000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 2.4, 'dim' => '28x23x9 cm', 'install' => false, 'certif' => true],
            ['cat' => 'ventilateurs', 'nom' => 'Ventilateur médical de transport Getinge', 'marque' => 'Getinge', 'desc' => 'Ventilateur médical portable pour transport et soins intensifs, modes de ventilation multiples.', 'vente' => 4200000, 'loc_j' => 15000, 'loc_s' => 90000, 'loc_m' => 300000, 'caution' => 500000, 'poids' => 12, 'dim' => '35x30x20 cm', 'install' => true, 'certif' => true],

            // Rééducation
            ['cat' => 'velos-de-reeducation', 'nom' => 'Vélo de rééducation pour membres inférieurs', 'marque' => 'Invacare', 'desc' => 'Pédalier de rééducation motorisé, résistance réglable, pour la rééducation à domicile.', 'vente' => 165000, 'loc_j' => 1500, 'loc_s' => 8000, 'loc_m' => 27000, 'caution' => 40000, 'poids' => 8, 'dim' => '55x35x30 cm', 'install' => false, 'certif' => false],
            ['cat' => 'tables-de-kinesitherapie', 'nom' => 'Table de kinésithérapie électrique 2 plans', 'marque' => 'Invacare', 'desc' => 'Table de massage et kinésithérapie à hauteur électrique variable, revêtement simili-cuir.', 'vente' => 385000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 45, 'dim' => '190x60x90 cm', 'install' => true, 'certif' => false],

            // Soins à domicile
            ['cat' => 'chaises-de-douche', 'nom' => 'Chaise de douche pliante avec accoudoirs', 'marque' => 'Invacare', 'desc' => 'Chaise de douche ajustable en hauteur, pieds antidérapants, structure aluminium anti-corrosion.', 'vente' => 42000, 'loc_j' => 500, 'loc_s' => 3000, 'loc_m' => 10000, 'caution' => 15000, 'poids' => 3.8, 'dim' => '50x45x90 cm', 'install' => false, 'certif' => false],
            ['cat' => 'coussins-anti-escarres', 'nom' => 'Coussin anti-escarres à mémoire de forme', 'marque' => 'Invacare', 'desc' => 'Coussin de positionnement en mousse viscoélastique pour la prévention des escarres en position assise.', 'vente' => 35000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 1.2, 'dim' => '45x40x8 cm', 'install' => false, 'certif' => false],

            // Consommables
            ['cat' => 'gants-et-masques', 'nom' => 'Boîte de 100 gants nitrile non poudrés', 'marque' => '3M', 'desc' => 'Gants d\'examen en nitrile, sans latex, résistants aux perforations. Boîte de 100 pièces.', 'vente' => 8500, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.4, 'dim' => '24x12x9 cm', 'install' => false, 'certif' => false],
            ['cat' => 'pansements', 'nom' => 'Lot de pansements stériles assortis', 'marque' => '3M', 'desc' => 'Assortiment de pansements adhésifs stériles pour petites plaies, plusieurs tailles.', 'vente' => 4500, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.2, 'dim' => '15x10x5 cm', 'install' => false, 'certif' => false],
            ['cat' => 'desinfectants', 'nom' => 'Solution hydroalcoolique 500ml', 'marque' => '3M', 'desc' => 'Gel désinfectant pour les mains, formule dermatologique, flacon pompe 500ml.', 'vente' => 3000, 'loc_j' => null, 'loc_s' => null, 'loc_m' => null, 'caution' => null, 'poids' => 0.55, 'dim' => '8x8x20 cm', 'install' => false, 'certif' => false],
        ];

        foreach ($products as $p) {
            $category = Category::where('slug', $p['cat'])->first();
            if (!$category) continue;

            Product::create([
                'nom' => $p['nom'],
                'description' => $p['desc'],
                'category_id' => $category->id,
                'vendor_id' => $vendorId,
                'marque' => $p['marque'],
                'reference' => 'PROD-' . strtoupper(Str::random(10)),
                'type_disponibilite' => $p['loc_j'] ? 'les_deux' : 'vente',
                'necessite_installation' => $p['install'],
                'necessite_certification' => $p['certif'],
                'prix_vente' => $p['vente'],
                'prix_location_jour' => $p['loc_j'],
                'prix_location_semaine' => $p['loc_s'],
                'prix_location_mois' => $p['loc_m'],
                'caution_location' => $p['caution'],
                'poids' => $p['poids'],
                'dimensions' => $p['dim'],
                'couleurs' => $p['couleurs'] ?? null,
                'statut' => 'actif',
            ]);
        }
    }
}