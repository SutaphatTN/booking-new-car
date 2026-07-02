@php
    $isTopup = ($req->type ?? 'place') === 'topup';
    $lines = $isTopup ? $req->topupPlaces : $req->places;
    $total = $isTopup ? $lines->sum(fn($p) => (float) ($p->pending_extra ?? 0)) : $lines->sum('cost');
    $brandName = config("brand.names.{$req->brand}") ?? ('Brand ' . ($req->brand ?? '-'));
@endphp
@component('mail::message')
# {{ $isTopup ? 'ขออนุมัติเพิ่มงบประมาณ (สถานที่)' : 'ขออนุมัติค่าใช้จ่ายกิจกรรมการตลาด (สถานที่)' }}

เรียน คุณ{{ optional($req->approver)->full_name ?: (optional($req->approver)->name ?? 'ผู้อนุมัติ') }},

มี{{ $isTopup ? 'คำขออนุมัติเพิ่มงบประมาณ' : 'คำขออนุมัติสถานที่/ค่าใช้จ่ายกิจกรรมการตลาด' }} จำนวน **{{ $lines->count() }}** รายการ
- แบรนด์: **{{ $brandName }}**
- ผู้ขออนุมัติ: {{ optional($req->requester)->full_name ?: (optional($req->requester)->name ?? '-') }}
- ประจำเดือน: {{ $req->period ?? '-' }}
- {{ $isTopup ? 'ยอดที่ขอเพิ่ม' : 'ยอดรวม' }}: {{ number_format($total, 2) }} บาท

{{-- รายละเอียดทั้งหมดอยู่ในไฟล์ PDF ที่แนบมาในอีเมลนี้ --}}
รายละเอียดทั้งหมดอยู่ในลิงก์ที่แนบมาในอีเมลนี้

@component('mail::button', ['url' => $approveUrl])
เปิดหน้าอนุมัติ
@endcomponent

หรือคัดลอกลิงก์นี้ไปเปิดในเบราว์เซอร์:
{{ $approveUrl }}

ขอบคุณครับ
@endcomponent
