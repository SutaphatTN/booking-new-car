<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;

    public function __construct($saleCar)
    {
        $this->saleCar = $saleCar;
    }

    public function build()
    {
        return $this->subject($this->saleCar->is_pre_approval
            ? 'คำขออนุมัติเกินงบล่วงหน้าได้รับการอนุมัติแล้ว'
            : 'ใบจองได้รับการอนุมัติแล้ว')
            ->markdown('emails.sale-approved');
    }
}
