<?php

namespace App\Notifications;

use App\Models\FundraisingProgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundraisingCampaignNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FundraisingProgram $program)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Fundraising Campaign: ' . $this->program->title)
            ->line('Dear Parent,')
            ->line('We are excited to announce a new fundraising campaign:')
            ->line($this->program->title)
            ->line($this->program->description)
            ->line('Target Amount: ₦' . number_format($this->program->target_amount, 2))
            ->line('Current Progress: ₦' . number_format($this->program->amount_raised, 2) . ' (' . number_format(($this->program->amount_raised / $this->program->target_amount) * 100, 2) . '%)')
            ->action('Contribute Now', url('/parent/fundraising/' . $this->program->id))
            ->line('Thank you for your support!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'New fundraising campaign: ' . $this->program->title,
            'program_id' => $this->program->id,
            'target_amount' => $this->program->target_amount,
        ];
    }
}
