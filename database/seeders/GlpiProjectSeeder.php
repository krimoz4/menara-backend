<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GlpiProject;

class GlpiProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projets = [
            ['nom' => 'Migration Serveurs Cloud', 'responsable' => 'Akram Ouddir', 'statut' => 'En cours', 'progression' => 65, 'date_echeance' => '15 Août 2026'],
            ['nom' => 'Déploiement Fibre Optique', 'responsable' => 'El Mehdi Hmimssa', 'statut' => 'Terminé', 'progression' => 100, 'date_echeance' => '02 Juil 2026'],
            ['nom' => 'Audit Parc Informatique', 'responsable' => 'Aymane Namous', 'statut' => 'En attente', 'progression' => 10, 'date_echeance' => '30 Sept 2026'],
            ['nom' => 'Mise à jour Antivirus Global', 'responsable' => 'Akram Ouddir', 'statut' => 'En cours', 'progression' => 85, 'date_echeance' => '20 Juil 2026'],
        ];

        foreach ($projets as $projet) {
            GlpiProject::create($projet);
        }
    }
}
