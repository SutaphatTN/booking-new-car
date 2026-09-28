<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * แจ้งเตือน "ผู้อนุมัติตีกลับใบจอง" — ส่งเหตุผล + ข้อมูลใบจอง ให้ผู้รับไปแก้ไข
 *  $actionUrl : ลิงก์ให้ผู้รับกดทำต่อ (กรณีตีกลับให้ผู้อนุมัติขั้นก่อนหน้า) ; null = ให้ไปแก้ในระบบ
 */
class ApprovalReturnMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saleCar;
    public $reason;
    public $returnedBy;
    public $actionUrl;

    public function __construct($saleCar, $reason, $returnedBy, $actionUrl = null)
    {
        $this->saleCar    = $saleCar;
        $this->reason     = $reason;
        $this->returnedBy = $returnedBy;
        $this->actionUrl  = $actionUrl;
    }

    public function build()
    {
        $brandName = config("brand.names.{$this->saleCar->brand}") ?? '';

        $what = $this->saleCar->is_pre_approval ? 'คำขออนุมัติเกินงบล่วงหน้า' : 'ใบจอง';

        return $this->subject(
            'ตีกลับ' . $what . ($brandName ? " {$brandName}" : '') . ' — กรุณาตรวจสอบ/แก้ไข'
        )->markdown('emails.approval-return');
    }
}
