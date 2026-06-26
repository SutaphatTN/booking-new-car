<?php

namespace App\Mail;

use App\Models\SourcePlaceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SourcePlaceRevisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public SourcePlaceRequest $req;
    public ?string $reason;

    public function __construct(SourcePlaceRequest $req, ?string $reason = null)
    {
        $this->req    = $req;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'คำขออนุมัติสถานที่ถูกส่งกลับให้แก้ไข',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.source-place-revision',
            with: [
                'req'         => $this->req,
                'reason'      => $this->reason,
                'settingsUrl' => route('source.place.index'),
            ],
        );
    }
}
