@component('mail::message')
# ขออนุมัติแคมเปญ CK 

เรียน ผู้อนุมัติ (MD)

มีคำขออนุมัติแคมเปญประเภท **CK** ดังนี้

- **แบรนด์:** {{ $brandName }}
- **เดือน:** {{ $period }}
- **จำนวน:** {{ $approvals->count() }} รายการ
- **ยอดรวม (สุทธิ):** {{ number_format($total, 2) }} บาท

รายละเอียดแคมเปญทั้งหมดอยู่ใน **ไฟล์ PDF ที่แนบมา**

@component('mail::button', ['url' => route('campaign.ckApproval.email', $token)])
เปิดหน้าอนุมัติ ({{ $approvals->count() }} รายการ)
@endcomponent

ขอบคุณครับ
@endcomponent
