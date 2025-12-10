<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct($saleCar, $type)
    {
        $this->saleCar = $saleCar;
        $this->type = $type;
    }

    public function build()
    {
        return $this->subject(
            $this->type === 'normal'
                ? 'ขออนุมัติยอดปกติ'
                : 'ขออนุมัติเกินงบ'
        )
            ->markdown('emails.sale-request');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ขออนุมัติใบจอง',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.sale-request',
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
