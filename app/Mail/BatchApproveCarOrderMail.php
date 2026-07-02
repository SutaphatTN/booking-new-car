<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BatchApproveCarOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $items;
    public $approverName;
    public $brand;

    /**
     * @param array    $items        รายการที่ขออนุมัติ (order_code, model, subModel, color, year, type, qty)
     * @param string   $approverName ชื่อผู้อนุมัติ
     * @param int|null $brand        brand ของคำขอ (สำหรับแสดงหัวอีเมล/subject)
     */
    public function __construct(array $items, string $approverName, $brand = null)
    {
        $this->items = $items;
        $this->approverName = $approverName;
        $this->brand = $brand;
    }

    public function build()
    {
        $brandName = config("brand.names.{$this->brand}") ?? ('Brand ' . ($this->brand ?? '-'));

        return $this->subject("[{$brandName}] มีคำขออนุมัติสั่งซื้อรถ")
            ->markdown('emails.batch-approve-order');
    }
}
