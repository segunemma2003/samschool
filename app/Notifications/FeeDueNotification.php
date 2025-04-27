<?php

namespace App\Notifications;

use App\Models\SchoolInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SchoolInvoice $invoice)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('School Fee Due Notification')
            ->line('Dear Parent,')
            ->line('This is to notify you that the school fee for ' . $this->invoice->student->name . ' is due.')
            ->line('Total Amount: ₦' . number_format($this->invoice->total_amount, 2))
            ->line('Due Date: ' . $this->invoice->due_date->format('d/m/Y'))
            ->action('View Invoice', url('/parent/invoices/' . $this->invoice->id))
            ->line('Thank you for your prompt payment.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Fee due for ' . $this->invoice->student->name,
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->total_amount,
            'due_date' => $this->invoice->due_date,
        ];
    }
}
