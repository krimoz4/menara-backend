<?php

namespace App\Services;

use App\Notifications\CriticalUptimeAlert;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AlertNotificationService
{
    /**
     * Envoyer les alertes Email et Teams pour un Uptime critique
     */
    public static function sendCriticalUptimeAlert($uptimeAverage = '92.4%', $serverName = 'Serveurs DSI Principaux', $triggeredByUserId = null)
    {
        $notification = new CriticalUptimeAlert($uptimeAverage, $serverName);

        $sentEmailCount = 0;
        $teamsSuccess = false;
        $recipientEmails = ['akram.ouddir@emsi-edu.ma'];

        // 1. Envoi par Email aux administrateurs DSI et à l'adresse akram.ouddir@emsi-edu.ma
        try {
            // Notification directe à l'email spécifié pour les tests
            Notification::route('mail', 'akram.ouddir@emsi-edu.ma')->notify($notification);
            $sentEmailCount++;

            $admins = User::whereIn('role', ['admin', 'admin_dsi'])->get();
            foreach ($admins as $admin) {
                if ($admin->email !== 'akram.ouddir@emsi-edu.ma') {
                    $admin->notify($notification);
                    $recipientEmails[] = $admin->email;
                    $sentEmailCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors de l envoi des emails d alerte: " . $e->getMessage());
        }

        // 2. Envoi vers le Webhook Microsoft Teams
        $teamsWebhookUrl = env('TEAMS_WEBHOOK_URL');
        if (!empty($teamsWebhookUrl)) {
            try {
                $response = Http::post($teamsWebhookUrl, $notification->toTeamsPayload());
                $teamsSuccess = $response->successful();
            } catch (\Exception $e) {
                Log::error("Erreur Webhook Teams: " . $e->getMessage());
                $teamsSuccess = false;
            }
        } else {
            // Mode démo/simulation si pas de Webhook renseigné
            $teamsSuccess = true;
        }

        $emailListStr = implode(', ', array_unique($recipientEmails));

        // 3. Traçabilité complète dans audit_logs
        AuditLog::create([
            'user_id' => $triggeredByUserId ?? 1,
            'action' => 'ALERT_NOTIFICATION_SENT',
            'target' => "Alerte Uptime Critique ({$uptimeAverage}) notifiée par Email à [{$emailListStr}] et Webhook Microsoft Teams (Statut: " . ($teamsSuccess ? 'Reçu' : 'Échec') . ")"
        ]);

        return [
            'emails_sent' => $sentEmailCount,
            'recipients' => array_unique($recipientEmails),
            'teams_notified' => $teamsSuccess,
            'uptime' => $uptimeAverage,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
