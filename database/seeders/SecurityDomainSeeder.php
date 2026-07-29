<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityDomain;

class SecurityDomainSeeder extends Seeder
{
    public function run(): void
    {
        $domaines = [
            ['domaine' => 'Politiques', 'score' => 95],
            ['domaine' => 'RH', 'score' => 85],
            ['domaine' => 'Actifs', 'score' => 70],
            ['domaine' => 'Accès', 'score' => 90],
            ['domaine' => 'Cryptographie', 'score' => 100],
            ['domaine' => 'Opérations', 'score' => 80],
        ];

        foreach ($domaines as $domaine) {
            SecurityDomain::firstOrCreate(['domaine' => $domaine['domaine']], ['score' => $domaine['score']]);
        }
    }
}
