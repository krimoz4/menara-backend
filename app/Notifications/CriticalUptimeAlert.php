<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CriticalUptimeAlert extends Notification
{
    use Queueable;

    public $uptimeAverage;
    public $serverName;
    public $timestamp;

    /**
     * Create a new notification instance.
     */
    public function __construct($uptimeAverage = '92.4%', $serverName = 'Serveurs Principaux DSI Ménara')
    {
        $this->uptimeAverage = $uptimeAverage;
        $this->serverName = $serverName;
        $this->timestamp = now()->format('d/m/Y H:i:s');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->priority(1)
            ->subject("[ALERTE CRITIQUE DSI] Dégradation de l Uptime ({$this->uptimeAverage}) - Ménara Holding")
            ->greeting('Bonjour Responsable DSI,')
            ->line("⚠️ **Une alerte critique de disponibilité système a été déclenchée.**")
            ->line("L Uptime moyen actuel est tombé à **{$this->uptimeAverage}**, ce qui est inférieur au seuil minimal exigé de **95.0%**.")
            ->line("• **Composant impacté** : {$this->serverName}")
            ->line("• **Date et Heure du déclenchement** : {$this->timestamp}")
            ->action('Ouvrir le Tableau de Bord DSI', config('app.url') . '/dashboard')
            ->line("Une intervention immédiate des équipes d infrastructure est recommandée.")
            ->salutation('Direction des Systèmes d Information - Ménara Holding');
    }

    /**
     * Payload pour Webhook Microsoft Teams (MessageCard JSON)
     */
    public function toTeamsPayload(): array
    {
        return [
            '@type' => 'MessageCard',
            '@context' => 'http://schema.org/extensions',
            'themeColor' => 'D9381E', // Rouge Alerte
            'summary' => "Alerte Uptime Critique DSI - {$this->uptimeAverage}",
            'title' => "🚨 [ALERTE DSI] Dégradation Uptime Système ({$this->uptimeAverage})",
            'sections' => [
                [
                    'activityTitle' => 'Ménara Holding - Monitoring Infrastructure',
                    'activitySubtitle' => "Horodatage : {$this->timestamp}",
                    'activityImage' => 'https://img.icons8.com/color/96/000000/warning-shield.png',
                    'facts' => [
                        ['name' => 'Disponibilité Actuelle :', 'value' => $this->uptimeAverage],
                        ['name' => 'Seuil Requis :', 'value' => '95.0%'],
                        ['name' => 'Composant Concerné :', 'value' => $this->serverName],
                        ['name' => 'Niveau de Sécurité :', 'value' => 'Critique (ISO 27001)']
                    ],
                    'markdown' => true
                ]
            ],
            'potentialAction' => [
                [
                    '@type' => 'OpenUri',
                    'name' => 'Consulter le Tableau de Bord',
                    'targets' => [
                        ['os' => 'default', 'uri' => config('app.url') . '/dashboard']
                    ]
                ]
            ]
        ];
    }
}
