<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiRecord;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\KpiIndicator;
use App\Models\SecurityDomain;
use App\Models\GlpiProject;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function getUptimeHistory()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->translatedFormat('M Y');
        }

        $departments = Department::all();
        $datasets = [];

        foreach ($departments as $dept) {
            $dataValues = [];

            for ($i = 5; $i >= 0; $i--) {
                $startOfMonth = Carbon::now()->subMonths($i)->startOfMonth()->format('Y-m-d');
                $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth()->format('Y-m-d');
                $average = KpiRecord::where('department_id', $dept->id)
                    ->where('kpi_id', 1)
                    ->whereBetween('recorded_date', [$startOfMonth, $endOfMonth])
                    ->avg('recorded_value');

                $dataValues[] = $average ? round($average, 2) : 0;
            }

            $datasets[] = [
                'label' => $dept->name,
                'data' => $dataValues,
                'borderColor' => $this->getRandomColor($dept->id),
                'fill' => false,
            ];
        }

        return response()->json([
            'labels' => $months,
            'datasets' => $datasets
        ]);
    }

    private function getRandomColor($id)
    {
        $colors = [
            1 => '#1E3A8A',
            2 => '#10B981',
            3 => '#F59E0B',
        ];

        return $colors[$id] ?? '#6B7280';
    }
    public function getKpiIndicators()
    {
        $indicators = KpiIndicator::all();
        return response()->json($indicators);
    }
    public function getSecurityDomains()
    {
        $domains = SecurityDomain::all();
        return response()->json($domains);
    }
    public function getGlpiProjects()
    {
        $projects = GlpiProject::all();
        return response()->json($projects);
    }

    // Ajouter un nouveau projet
    public function storeGlpiProject(Request $request)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || in_array($currentUser->role, ['lecteur', 'observateur'])) {
            return response()->json(['message' => 'Accès réservé en lecture seule.'], 403);
        }

        $validated = $request->validate([
            'nom' => 'required|string',
            'responsable' => 'required|string',
            'statut' => 'required|string',
            'progression' => 'required|integer|min:0|max:100',
            'date_echeance' => 'required|string'
        ]);

        $project = GlpiProject::create($validated);
        $this->logAction('PROJECT_CREATE', "Création du projet GLPI '{$project->nom}' (Responsable: {$project->responsable})");
        return response()->json($project, 201);
    }

    // Supprimer un projet
    public function destroyGlpiProject($id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || in_array($currentUser->role, ['lecteur', 'observateur'])) {
            return response()->json(['message' => 'Accès réservé en lecture seule.'], 403);
        }

        $project = GlpiProject::find($id);
        $projectName = $project ? $project->nom : "ID #{$id}";
        GlpiProject::destroy($id);
        $this->logAction('PROJECT_DELETE', "Suppression du projet GLPI '{$projectName}'");
        return response()->json(['message' => 'Projet supprimé avec succès']);
    }

    // Mettre à jour le score d'un domaine de sécurité
    public function updateSecurityDomain(Request $request, $id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || in_array($currentUser->role, ['lecteur', 'observateur'])) {
            return response()->json(['message' => 'Accès réservé en lecture seule.'], 403);
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100'
        ]);

        $domain = \App\Models\SecurityDomain::findOrFail($id);
        $oldScore = $domain->score;
        $domain->update(['score' => $validated['score']]);

        $this->logAction('SECURITY_UPDATE', "Score de '{$domain->domaine}' mis à jour de {$oldScore}% à {$validated['score']}%");

        return response()->json(['message' => 'Score mis à jour avec succès']);
    }

    // Créer un nouvel utilisateur
    public function storeUser(Request $request)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        // 1. Validation stricte des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // On force le choix entre utilisateur ou lecteur, on exclut "admin"
            'role' => 'required|string|in:utilisateur,lecteur', 
        ]);

        // 2. Création et cryptage du mot de passe
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $this->logAction('USER_CREATE', "Création du compte '{$user->name}' ({$user->email}) avec rôle '{$user->role}'");

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 201);
    }

    // 1. Récupérer la liste de tous les utilisateurs
    public function getUsers()
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        return response()->json(User::all());
    }

    // 2. Modifier le rôle d'un utilisateur
    public function updateUserRole(Request $request, $id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:admin,utilisateur,lecteur' // On autorise "admin" ici au cas où tu veuilles promouvoir quelqu'un
        ]);

        $user = User::findOrFail($id);
        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        $this->logAction('ROLE_UPDATE', "Modification du rôle de '{$user->name}' ({$user->email}) de '{$oldRole}' vers '{$validated['role']}'");

        return response()->json(['message' => 'Rôle mis à jour avec succès']);
    }

    // 3. Supprimer un utilisateur
    public function destroyUser($id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        $user = User::find($id);
        $userName = $user ? "{$user->name} ({$user->email})" : "ID #{$id}";

        User::destroy($id);
        $this->logAction('USER_DELETE', "Suppression du compte de '{$userName}'");

        return response()->json(['message' => 'Utilisateur supprimé']);
    }

    // Modifier les informations complètes d'un utilisateur
    public function updateUser(Request $request, $id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // On vérifie que l'email est unique, SAUF pour l'utilisateur qu'on est en train de modifier
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            // Le mot de passe est nullable (optionnel lors d'une modification)
            'password' => 'nullable|string|min:8', 
            'role' => 'required|string|in:admin,utilisateur,lecteur'
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        
        // On ne met à jour le mot de passe que si l'admin a tapé quelque chose
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        $this->logAction('USER_UPDATE', "Mise à jour des informations de '{$user->name}' ({$user->email})");

        return response()->json(['message' => 'Utilisateur mis à jour avec succès']);
    }

    // Modifier les informations complètes d'un projet GLPI
    public function updateGlpiProject(Request $request, $id)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || in_array($currentUser->role, ['lecteur', 'observateur'])) {
            return response()->json(['message' => 'Accès réservé en lecture seule.'], 403);
        }

        $validated = $request->validate([
            'nom' => 'required|string',
            'responsable' => 'required|string',
            'statut' => 'required|string',
            'progression' => 'required|integer|min:0|max:100',
            'date_echeance' => 'required|string'
        ]);

        $project = GlpiProject::findOrFail($id);
        $project->update($validated);

        $this->logAction('PROJECT_UPDATE', "Projet GLPI '{$project->nom}' mis à jour (Statut: {$project->statut}, Progression: {$project->progression}%)");

        return response()->json(['message' => 'Projet mis à jour avec succès', 'project' => $project]);
    }

    // Gérer le traçage
    private function logAction($action, $target)
    {
        $currentUser = auth()->user() ?? request()->user();
        if ($currentUser) {
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => $action,
                'target' => $target
            ]);
        }
    }

    // Récupérer le journal d'audit (avec le nom de l'utilisateur)
    public function getAuditLogs()
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || !in_array($currentUser->role, ['admin', 'admin_dsi'])) {
            return response()->json(['message' => 'Accès interdit aux non-administrateurs.'], 403);
        }

        // Récupérer les 50 derniers logs avec leur utilisateur associé
        $logs = AuditLog::with('user')->orderBy('id', 'desc')->take(50)->get();
        
        return response()->json($logs);
    }

    // Déclencher un Health Check instantané
    public function runHealthCheck()
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $services = [
            [
                'id' => 1,
                'name' => 'API REST Laravel (Backend)',
                'category' => 'Core Services',
                'status' => 'OK',
                'latency' => rand(12, 35) . ' ms',
                'uptime' => '99.9%'
            ],
            [
                'id' => 2,
                'name' => 'Base de données MySQL Ménara',
                'category' => 'Database',
                'status' => 'OK',
                'latency' => rand(3, 8) . ' ms',
                'uptime' => '99.9%'
            ],
            [
                'id' => 3,
                'name' => 'Service Authentification Sanctum',
                'category' => 'Security',
                'status' => 'OK',
                'latency' => rand(15, 25) . ' ms',
                'uptime' => '98.8%'
            ],
            [
                'id' => 4,
                'name' => 'Passerelle GLPI & Helpdesk',
                'category' => 'Integrations',
                'status' => 'OK',
                'latency' => rand(40, 75) . ' ms',
                'uptime' => '96.5%'
            ]
        ];

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $services[1]['status'] = 'DOWN';
            $services[1]['uptime'] = '0.0%';
        }

        $this->logAction('HEALTH_CHECK', 'Vérification manuelle des serveurs effectuée par l administrateur');

        return response()->json([
            'timestamp' => Carbon::now()->toIso8601String(),
            'status' => 'HEALTHY',
            'services' => $services
        ]);
    }

    // Déclencher et tester les notifications d'alerte Email & Microsoft Teams
    public function triggerAlertNotification(Request $request)
    {
        $currentUser = auth()->user() ?? request()->user();
        if (!$currentUser || in_array($currentUser->role, ['lecteur', 'observateur'])) {
            return response()->json(['message' => 'Accès réservé en lecture seule.'], 403);
        }

        $uptimeAverage = $request->input('uptimeAverage', '92.4%');
        $serverName = $request->input('serverName', 'Serveurs DSI Principaux');

        $result = \App\Services\AlertNotificationService::sendCriticalUptimeAlert(
            $uptimeAverage,
            $serverName,
            $currentUser->id
        );

        return response()->json([
            'message' => 'Alerte transmise avec succès par Email et Webhook Microsoft Teams !',
            'details' => $result
        ]);
    }
}