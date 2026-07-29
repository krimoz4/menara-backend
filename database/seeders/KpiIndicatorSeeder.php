<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiIndicator;

class KpiIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        KpiIndicator::create([
            'objectif' => 'Assurer la disponibilité des ressources informatiques.',
            'indicateur' => 'Taux de disponibilité du Système.',
            'type_indicateur' => 'Performance',
            'cible_texte' => '>=95%',
            'cible_valeur' => 95.0,
            'm1' => 98, 'm2' => 97, 'm3' => 96, 'm4' => 96, 'm5' => 95, 'm6' => 97,
        ]);

        KpiIndicator::create([
            'objectif' => 'Améliorer le temps de réponse des interventions.',
            'indicateur' => 'Taux de résolution des incidents dans les délais (par catégorie).',
            'type_indicateur' => 'Performance',
            'cible_texte' => '>=75%',
            'cible_valeur' => 75.0,
            'm1' => 89, 'm2' => 87, 'm3' => 91, 'm4' => 92, 'm5' => 97,
        ]);

        KpiIndicator::create([
            'objectif' => 'Assurer la réalisation des projets GLPI dans les délais.',
            'indicateur' => 'Taux de succès des projets.',
            'type_indicateur' => 'Performance',
            'cible_texte' => '>=80%',
            'cible_valeur' => 80.0,
        ]);
    }
}