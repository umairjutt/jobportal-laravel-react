<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a candidate when a recruiter moves their application to a new stage.
 */
class ApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public JobApplication $application,
        public string $fromStage,
        public string $toStage,
    ) {}

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
        $this->application->loadMissing('job');

        return [
            'type' => 'application_status',
            'application_id' => $this->application->id,
            'job_id' => $this->application->job_id,
            'job_title' => $this->application->job?->title,
            'from_stage' => $this->fromStage,
            'to_stage' => $this->toStage,
            'message' => sprintf(
                'Your application for %s moved to "%s"',
                $this->application->job?->title ?? 'a job',
                $this->toStage,
            ),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
