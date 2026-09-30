<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Mobilité' => ['Fauteuils roulants', 'Déambulateurs et cannes', 'Scooters médicalisés', 'Lève-personnes'],
            'Lits et mobilier médical' => ['Lits médicalisés', 'Matelas anti-escarres', 'Fauteuils de repos'],
            'Respiration et oxygénothérapie' => ['Concentrateurs d\'oxygène', 'Appareils CPAP/BiPAP', 'Aspirateurs de mucosités'],
            'Diagnostic et mesure' => ['Tensiomètres', 'Oxymètres', 'Glucomètres', 'Balances médicales'],
            'Imagerie médicale' => ['Radiographie', 'Échographes', 'Scanners', 'IRM'],
            'Bloc opératoire' => ['Tables d\'opération', 'Moniteurs multiparamétriques', 'Défibrillateurs', 'Ventilateurs'],
            'Rééducation' => ['Vélos de rééducation', 'Électrostimulation', 'Tables de kinésithérapie'],
            'Soins à domicile' => ['Chaises percées', 'Chaises de douche', 'Coussins anti-escarres'],
            'Consommables' => ['Gants et masques', 'Pansements', 'Seringues', 'Désinfectants'],
        ];

        foreach ($categories as $parent => $enfants) {
            $parentCat = Category::create([
                'nom' => $parent,
                'slug' => Str::slug($parent),
            ]);

            foreach ($enfants as $enfant) {
                Category::create([
                    'nom' => $enfant,
                    'slug' => Str::slug($enfant),
                    'parent_id' => $parentCat->id,
                ]);
            }
        }
    }
}
