<?php
namespace App\Mail;

use App\Models\RenewalOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalPaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RenewalOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[續約成功] 您的帳號已續約至 ' . $this->order->user->expires_at?->format('Y-m-d'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.renewal_payment_confirmed',
        );
    }
}
