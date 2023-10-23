<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyAdminsAboutPendingTransaction extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $admins;

    /**
     * Create a new message instance.
     */
    public function __construct($transaction, $admins)
    {
        $this->transaction = $transaction;
        $this->admins = $admins;
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
        $amount = $this->transaction->amount;
        $address = $address = " Plot 157 Ssebowa Road,Kiwatule, Nakawa division, <br />
        Kampala – Uganda (East Africa). <br />
        P.O. Box 3513, Kampala (Uganda).";
        $institution = $this->transaction->institution->institution_name;


        return new Content(
            view: 'emails.transaction.notify_admins_pending',
            with: [
                'amount' => 'Ush ' . number_format($amount),
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
