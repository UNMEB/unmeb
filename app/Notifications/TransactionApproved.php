<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NumberFormatter;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class TransactionApproved extends Notification
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [DashboardChannel::class];
    }

    public function toDashboard($notifiable)
    {

        $amountInWords = (new NumberFormatter('en_US', NumberFormatter::SPELLOUT))->format($this->transaction->amount);
        
        return (new DashboardMessage)
            ->title('UNMEB OSRS - Account credited with Ush '. number_format($this->transaction->amount))
            ->message('Your UNMEB OSRS account has been credited with ' . $amountInWords)
            ->action(route('platform.systems.finance.complete')); // Adjust the URL according to your application logic
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}