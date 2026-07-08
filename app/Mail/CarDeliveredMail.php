<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * แจ้งเตือน "ส่งมอบรถแล้ว" — ส่งข้อมูลลูกค้า/รถ/VIN เพื่อให้ผู้รับไปจบยอดที่ธนาคาร
 */
class CarDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;

    public function __construct($saleCar)
    {
        $this->saleCar = $saleCar;
    }

    public function build()
    {
        $vin = $this->saleCar->carOrder->vin_number ?? '';

        return $this->subject('แจ้งส่งมอบรถ — จบยอดที่ธนาคาร' . ($vin ? " (VIN {$vin})" : ''))
            ->markdown('emails.car-delivered');
    }
}
