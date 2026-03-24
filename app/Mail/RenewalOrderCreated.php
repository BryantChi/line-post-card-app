<?php
namespace App\Mail;

use App\Models\RenewalOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RenewalOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[續約通知] 新增匯款訂單 ' . $this->order->order_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.renewal_order_created',
        );
    }
}
