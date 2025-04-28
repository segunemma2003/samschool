<?php

namespace App\Notifications;

use App\Models\ProgramContribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContributionApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProgramContribution $contribution)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Contribution Approved')
            ->line('Your contribution of ₦' . number_format($this->contribution->amount, 2) . ' has been approved.')
            ->line('Program: ' . $this->contribution->fundraisingProgram->title)
            ->action('View Program', url('/parent/fundraising/' . $this->contribution->fundraising_program_id))
            ->line('Thank you for your support!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Contribution of ₦' . number_format($this->contribution->amount, 2) . ' approved',
            'program_id' => $this->contribution->fundraising_program_id,
            'program_title' => $this->contribution->fundraisingProgram->title,
        ];
    }
}
