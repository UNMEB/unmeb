<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use NumberFormatter;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Orchid\Platform\Models\Role;

class NotifyAccountsAboutPendingTransaction extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $accountants;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, $accountants)
    {
        $this->transaction = $transaction;
        $this->accountants = $accountants;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'UNMEB OSRS - Pending Transaction Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {



        // Amount
        $amount = $this->transaction->amount;

        // Amount in words
        $amountInWords = (new NumberFormatter('en_US', NumberFormatter::SPELLOUT))->format($amount);


        // Html for address
        $address = " Plot 157 Ssebowa Road,Kiwatule, Nakawa division, <br />

          Kampala –Uganda (East Africa). <br />

          P.O. Box 3513, Kampala (Uganda).";

        return new Content(
            view: 'emails.transaction.notify_accounts_pending',
            with: [
                'amount' => 'Ush ' . number_format($amount),
                'amountInWords' => Str::title($amountInWords),
                'address'   => $address,
                'institution' => $this->transaction->institution->institution_name,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
