<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ขอให้ IA (GM/MD) ตรวจสอบรายการใบจอง
 *
 * ต่างจาก SaleRequestMail ตรงที่ไม่มีปุ่มอนุมัติในเมล — ผู้รับต้องเข้าไปติ๊ก
 * "ตรวจสอบรายการ (IA)" ในหน้าใบจองจริง ลิงก์จึงพาไปหน้านั้นโดยตรง (ดู PurchaseOrderController::iaReview)
 */
class IaCheckRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;
    public $token;
    public $requestedBy;

    public function __construct($saleCar, string $token, ?string $requestedBy = null)
    {
        $this->saleCar     = $saleCar;
        $this->token       = $token;
        $this->requestedBy = $requestedBy;
    }

    public function envelope(): Envelope
    {
        $customer = trim(
            ($this->saleCar->customer->prefix->Name_TH ?? '') . ' ' .
                ($this->saleCar->customer->FirstName ?? '') . ' ' .
                ($this->saleCar->customer->LastName ?? '')
        );

        return new Envelope(
            subject: 'ขอให้ตรวจสอบรายการ (IA) — ใบจอง #' . $this->saleCar->id . ($customer ? " : {$customer}" : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ia-check-request',
        );
    }
}
