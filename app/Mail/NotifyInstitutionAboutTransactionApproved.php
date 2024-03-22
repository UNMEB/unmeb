<?php

namespace App\Mail;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Str;
use NumberFormatter;

class NotifyInstitutionAboutTransactionApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'UNMEB OSRS - Transaction Approved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $amount = $this->transaction->amount;

        // Html for address
        $address = " Plot 157 Ssebowa Road,Kiwatule, Nakawa division, <br />

          Kampala –Uganda (East Africa). <br />

          P.O. Box 3513, Kampala (Uganda).";

        return new Content(
            view: 'emails.transaction.notify_institution_approved',
            with: [
                'amount' => 'Ush ' . number_format($amount),
                'address' => $address,
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

        // Amount
        $amount = $this->transaction->amount;

        // Amount in words
        $amountInWords = (new NumberFormatter('en_US', NumberFormatter::SPELLOUT))->format($amount);

        // $transactionDate = Carbon::createFromFormat('d/m/Y', $this->transaction->updated_at);

        // Html for address
        $address = " Plot 157 Ssebowa Road,Kiwatule, Nakawa division, <br />

        Kampala –Uganda (East Africa). <br />

        P.O. Box 3513, Kampala (Uganda).";

        $settings = \Config::get('settings');

        $receiptData = [
            'amount' => 'Ush ' . number_format($amount),
            'amountInWords' => Str::title($amountInWords),
            'address' => $address,
            'approvedBy' => $this->transaction->approvedBy->name,
            'institution' => $this->transaction->institution->institution_name,
            'finance_signature' => $settings['signature.finance_signature'] ?? '',
            'status' => $this->transaction->status,
            'date' => $this->transaction->updated_at,
        ];

        $pdf = Pdf::loadView('receipt', $receiptData);

        // Create the attachment


        return [
            Attachment::fromData(function () use ($pdf) {
                return $pdf->output();
            }, 'payment_receipt.pdf')->withMime('application/pdf'),
        ];
    }
}
