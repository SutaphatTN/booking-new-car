@php
    $isTopup = ($req->type ?? 'place') === 'topup';
    $lines = $isTopup ? $req->topupPlaces : $req->places;
    $total = $isTopup ? $lines->sum(fn($p) => (float) ($p->pending_extra ?? 0)) : $lines->sum('cost');
@endphp
@component('mail::message')
# {{ $isTopup ? 'คำขออนุมัติเพิ่มงบประมาณได้รับการอนุมัติแล้ว' : 'คำขออนุมัติสถานที่ได้รับการอนุมัติแล้ว' }}

เรียน คุณ {{ optional($req->requester)->full_name ?: (optional($req->requester)->name ?? 'ผู้ขออนุมัติ') }},

{{ $isTopup ? 'คำขออนุมัติเพิ่มงบประมาณ' : 'คำขออนุมัติสถานที่/ค่าใช้จ่ายกิจกรรมการตลาด' }} จำนวน **{{ $lines->count() }}** รายการ ได้รับการอนุมัติเรียบร้อยแล้ว
- ผู้อนุมัติ: {{ optional($req->approver)->full_name ?: (optional($req->approver)->name ?? '-') }}
- ประจำเดือน: {{ $req->period ?? '-' }}
- {{ $isTopup ? 'ยอดที่อนุมัติเพิ่ม' : 'ยอดรวม' }}: {{ number_format($total, 2) }} บาท

รายละเอียดทั้งหมดอยู่ในไฟล์ PDF ที่แนบมาในอีเมลนี้

ขอบคุณครับ
@endcomponent
