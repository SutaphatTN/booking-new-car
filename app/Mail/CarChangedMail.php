<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * แจ้งเตือน "เปลี่ยนคันรถ" — ส่งเมื่อใบจองสลับคันหลังจากเมลแจ้งส่งมอบของคันเดิมออกไปแล้ว
 * ปลายทางจะได้รู้ว่า VIN เดิมไม่ได้ส่งมอบ ต้องไปจบยอดที่ธนาคารตาม VIN ใหม่แทน
 */
class CarChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;
    public $oldCarOrder;
    public $newCarOrder;

    public function __construct($saleCar, $oldCarOrder, $newCarOrder = null)
    {
        $this->saleCar     = $saleCar;
        $this->oldCarOrder = $oldCarOrder;
        $this->newCarOrder = $newCarOrder;
    }

    public function build()
    {
        $oldVin    = $this->oldCarOrder->vin_number ?? '';
        $newVin    = $this->newCarOrder->vin_number ?? '';
        $brandName = config("brand.names.{$this->saleCar->brand}") ?? '';

        return $this->subject(
            'แจ้งเปลี่ยนคันรถ'
                . ($brandName ? " {$brandName}" : '')
                . ' — ยกเลิกการจบยอด'
                . ($oldVin ? " VIN {$oldVin}" : '')
                . ($newVin ? " → VIN {$newVin}" : ' (ยังไม่ผูกคันใหม่)')
        )->markdown('emails.car-changed');
    }
}
