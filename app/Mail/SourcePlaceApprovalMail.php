<?php

namespace App\Mail;

use App\Models\SourcePlaceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SourcePlaceApprovalMail extends Mailable
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
            subject: 'ขออนุมัติค่าใช้จ่ายกิจกรรมการตลาด (สถานที่)',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.source-place-approval',
            with: [
                'req'        => $this->req,
                'approveUrl' => route('source.approval', $this->req->token),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, 'approval-' . $this->req->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
