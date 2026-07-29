<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\KpiCategory;
use App\Models\Kpi;
use App\Models\KpiRecord;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dsi = Department::firstOrCreate(['name' => 'DSI Centrale'], ['description' => 'Gouvernance et SI Global']);
        $prefa = Department::firstOrCreate(['name' => 'Ménara Préfa'], ['description' => 'Pôle BTP et Industrie']);
        $transport = Department::firstOrCreate(['name' => 'Ménara Transport'], ['description' => 'Pôle Logistique']);

        User::firstOrCreate(
            ['email' => 'admin@menara-holding.ma'],
            [
                'name' => 'Admin DSI',
                'password' => Hash::make('123456'),
                'department_id' => $dsi->id,
                'role' => 'admin_dsi'
            ]
        );

        User::firstOrCreate(
            ['email' => 'akram.ouddir@emsi-edu.ma'],
            [
                'name' => 'Akram Ouddir',
                'password' => Hash::make('123456'),
                'department_id' => $dsi->id,
                'role' => 'admin_dsi'
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@menara-holding.ma'],
            [
                'name' => 'Gestionnaire DSI',
                'password' => Hash::make('123456'),
                'department_id' => $dsi->id,
                'role' => 'utilisateur'
            ]
        );

        User::firstOrCreate(
            ['email' => 'lecteur@menara-holding.ma'],
            [
                'name' => 'Consultant Lecteur',
                'password' => Hash::make('123456'),
                'department_id' => $dsi->id,
                'role' => 'lecteur'
            ]
        );

        $catSecu = KpiCategory::create(['name' => 'Sécurité & ISO 27001', 'icon' => 'ShieldCheck']);
        $catInfra = KpiCategory::create(['name' => 'Infrastructure & Réseau', 'icon' => 'Server']);
        $kpiUptime = Kpi::create([
            'kpi_category_id' => $catInfra->id,
            'name' => 'Taux de disponibilité serveurs (Uptime)',
            'unit' => '%',
            'target_value' => 99.90,
            'is_higher_better' => true
        ]);

        $kpiIncidents = Kpi::create([
            'kpi_category_id' => $catSecu->id,
            'name' => 'Incidents critiques de sécurité',
            'unit' => 'Incidents',
            'target_value' => 0,
            'is_higher_better' => false
        ]);

        $departments = [$dsi, $prefa, $transport];

        for ($i = 6; $i >= 0; $i--) {
            $dateReleve = Carbon::now()->subMonths($i)->startOfMonth();

            foreach ($departments as $dept) {
                KpiRecord::create([
                    'kpi_id' => $kpiUptime->id,
                    'department_id' => $dept->id,
                    'recorded_value' => rand(9800, 10000) / 100, 
                    'recorded_date' => $dateReleve,
                    'notes' => rand(1, 10) > 8 ? 'Légère instabilité réseau détectée' : null
                ]);
                KpiRecord::create([
                    'kpi_id' => $kpiIncidents->id,
                    'department_id' => $dept->id,
                    'recorded_value' => rand(0, 100) > 85 ? rand(1, 2) : 0, 
                    'recorded_date' => $dateReleve,
                    'notes' => null
                ]);
            }
        }

        $this->call([
            KpiIndicatorSeeder::class,
            SecurityDomainSeeder::class,
            GlpiProjectSeeder::class,
        ]);
    }
}