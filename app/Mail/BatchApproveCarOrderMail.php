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

    /**
     * @param array  $items        รายการที่ขออนุมัติ (order_code, model, subModel, color, year, type, qty)
     * @param string $approverName ชื่อผู้อนุมัติ
     */
    public function __construct(array $items, string $approverName)
    {
        $this->items = $items;
        $this->approverName = $approverName;
    }

    public function build()
    {
        return $this->subject('มีคำขออนุมัติสั่งซื้อรถ')
            ->markdown('emails.batch-approve-order');
    }
}
