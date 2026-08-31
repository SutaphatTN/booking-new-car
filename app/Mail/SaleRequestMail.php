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
    public $data;
    public $files;

    /** token ที่จะใส่ในลิงก์ของเมลฉบับนี้ (null = ใช้ approval_token ปกติ) */
    public $linkToken;

    /** ฉบับสำเนา (CC) — ไม่มีปุ่มอนุมัติ มีแต่ลิงก์เปิดดู/ตีกลับ */
    public $isCopy;

    /**
     * @param array $files รายการไฟล์แนบ (Illuminate\Mail\Mailables\Attachment)
     */
    /**
     * @param string|null $linkToken  token สำหรับลิงก์ในเมลฉบับนี้ — แยกให้ผู้อนุมัติตัวจริง
     *                                กับคนที่ถูก CC ได้คนละตัว (ดู emailFinalApprover)
     * @param bool        $isCopy     true = ฉบับสำเนา ไม่ให้กดอนุมัติ
     */
    public function __construct($saleCar, $type, $data = null, array $files = [], $linkToken = null, bool $isCopy = false)
    {
        $this->saleCar = $saleCar;
        $this->type = $type;
        $this->data = $data;
        $this->files = $files;
        $this->linkToken = $linkToken;
        $this->isCopy = $isCopy;
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
        return $this->files ?? [];
    }
}
