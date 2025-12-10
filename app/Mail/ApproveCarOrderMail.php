<?php

namespace App\Mail;

use App\Models\CarOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApproveCarOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(CarOrder $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject('มีคำขออนุมัติสั่งซื้อรถใหม่')
            ->markdown('emails.approve-order');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'คำขอสั่งซื้อรถ',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.approve-order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
