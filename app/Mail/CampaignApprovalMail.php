<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CampaignApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $approvals;
    public $period;
    public $token;
    public $brandName;
    public $total;

    public function __construct($approvals, $period, $token)
    {
        $this->approvals = $approvals;
        $this->period    = $period;
        $this->token     = $token;
        $this->brandName = config('brand.names.' . ($approvals->first()->brand ?? 0), '-');
        $this->total     = $approvals->sum(fn($ap) => (float) ($ap->campaign->cashSupport_final ?? 0));
    }

    public function build()
    {
        return $this->subject('ขออนุมัติแคมเปญ CK — ' . $this->brandName . ' (เดือน ' . $this->period . ') ' . $this->approvals->count() . ' รายการ')
            ->markdown('emails.campaign-approval');
    }

    public function attachments(): array
    {
        // ข้อมูลเยอะ → แนบเป็น PDF แทนการยัดตารางยาวในเมล (กัน Gmail clip)
        $pdf = Pdf::loadView('campaign.approval.pdf', [
            'approvals' => $this->approvals,
            'period'    => $this->period,
            'brandName' => $this->brandName,
        ])->setPaper('A4', 'portrait');

        return [
            Attachment::fromData(fn() => $pdf->output(), 'ck-approval-' . $this->period . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
