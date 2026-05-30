<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a recruiter when a candidate applies to one of their jobs.
 * Persists to the database and pushes a live toast via the broadcast channel.
 */
class NewApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(public JobApplication $application) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->application->loadMissing(['job', 'candidate']);

        return [
            'type' => 'new_application',
            'application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job?->title,
            'candidate_name' => $this->application->candidate?->name,
            'message' => sprintf(
                '%s applied to %s',
                $this->application->candidate?->name ?? 'A candidate',
                $this->application->job?->title ?? 'your job',
            ),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
