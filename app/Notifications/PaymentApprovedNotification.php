<?php

namespace App\Notifications;

use App\Models\SchoolPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SchoolPayment $payment)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Approved')
            ->line('Dear Parent,')
            ->line('Your payment of ₦' . number_format($this->payment->amount, 2) . ' has been approved.')
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Transaction Reference: ' . $this->payment->transaction_reference)
            ->action('View Receipt', url('/parent/payments/' . $this->payment->id))
            ->line('Thank you for your payment.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Payment of ₦' . number_format($this->payment->amount, 2) . ' approved',
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
        ];
    }
}
