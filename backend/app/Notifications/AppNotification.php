<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Base of the app's in-app notifications (database channel → the SPA's bell).
 * Every notification carries the same shape:
 *   { title, body, link (an SPA path to open, or null), type (info | success | warning | error) }
 *
 *   $user->notify(new AppNotification('Report ready', 'Your export finished.', '/reports/12', 'success'));
 *
 * Subclass it for a named notification (RoleAssignedNotification) or to add
 * channels (mail) — override via().
 */
class AppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body = '',
        public ?string $link = null,
        public string $type = 'info',
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'link' => $this->link, 'type' => $this->type];
    }
}
