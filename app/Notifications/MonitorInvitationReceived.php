<?php

namespace App\Notifications;

use App\Models\{MonitorInvitation, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonitorInvitationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MonitorInvitation $invitation)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $this->invitation->loadMissing(['classGroup:id,course_id,number', 'classGroup.course:id,code,name', 'invitedBy:id,name']);
        $classGroup = $this->invitation->classGroup;

        return (new MailMessage())
            ->subject('Convite para ser monitor')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("{$this->invitation->invitedBy->name} convidou você para ser monitor de {$classGroup->course->code} {$classGroup->number} ({$classGroup->course->name}).")
            ->line('Ao aceitar, seu nome e seu perfil passam a aparecer para os alunos.')
            ->action('Ver convite', route('dashboard'));
    }
}
