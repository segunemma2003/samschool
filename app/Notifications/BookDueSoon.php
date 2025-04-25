<?php

namespace App\Notifications;

use App\Models\LibraryBookLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookDueSoon extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LibraryBookLoan $loan)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Book Due Soon: ' . $this->loan->book->title)
            ->line('The book "' . $this->loan->book->title . '" is due in 2 days.')
            ->line('Due date: ' . $this->loan->due_date->format('M d, Y'))
            ->action('View Your Loans', url('/library'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Book due soon: ' . $this->loan->book->title,
            'link' => '/library',
        ];
    }
}
