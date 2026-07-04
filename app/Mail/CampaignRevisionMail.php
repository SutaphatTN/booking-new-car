<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CampaignRevisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $approvals;
    public $period;
    public $reason;
    public $brandName;
    public $total;

    public function __construct($approvals, $period, $reason = null)
    {
        $this->approvals = $approvals;
        $this->period    = $period;
        $this->reason    = $reason;
        $this->brandName = config('brand.names.' . ($approvals->first()->brand ?? 0), '-');
        $this->total     = $approvals->sum(fn($ap) => (float) ($ap->campaign->cashSupport_final ?? 0));
    }

    public function build()
    {
        return $this->subject('แคมเปญ CK ถูกส่งกลับให้แก้ไข — ' . $this->brandName . ' (เดือน ' . $this->period . ')')
            ->markdown('emails.campaign-revision', ['pageUrl' => route('campaign.ckApproval')]);
    }

    public function attachments(): array
    {
        // แนบ PDF รายการแคมเปญ (เหมือนตอนขออนุมัติ)
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
