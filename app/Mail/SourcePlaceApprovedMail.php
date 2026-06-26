<?php

namespace App\Mail;

use App\Models\SourcePlaceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SourcePlaceApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public SourcePlaceRequest $req;
    public string $pdfData;

    public function __construct(SourcePlaceRequest $req, string $pdfData)
    {
        $this->req     = $req;
        $this->pdfData = $pdfData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'คำขออนุมัติสถานที่ได้รับการอนุมัติแล้ว',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.source-place-approved',
            with: [
                'req' => $this->req,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, 'approved-' . $this->req->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
