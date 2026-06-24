@component('mail::message')
# ขออนุมัติค่าใช้จ่ายกิจกรรมการตลาด (สถานที่)

เรียน คุณ{{ optional($req->approver)->full_name ?: (optional($req->approver)->name ?? 'ผู้อนุมัติ') }},

มีคำขออนุมัติสถานที่/ค่าใช้จ่ายกิจกรรมการตลาด จำนวน **{{ $req->places->count() }}** รายการ
- ผู้ขออนุมัติ: {{ optional($req->requester)->full_name ?: (optional($req->requester)->name ?? '-') }}
- ประจำเดือน: {{ $req->period ?? '-' }}
- ยอดรวม: {{ number_format($req->places->sum('cost'), 2) }} บาท

{{-- รายละเอียดทั้งหมดอยู่ในไฟล์ PDF ที่แนบมาในอีเมลนี้ --}}
รายละเอียดทั้งหมดอยู่ในลิงก์ที่แนบมาในอีเมลนี้

@component('mail::button', ['url' => $approveUrl])
เปิดหน้าอนุมัติ
@endcomponent

หรือคัดลอกลิงก์นี้ไปเปิดในเบราว์เซอร์:
{{ $approveUrl }}

ขอบคุณครับ
@endcomponent
