@php
    $isTopup = ($req->type ?? 'place') === 'topup';
    $lines = $isTopup ? $req->topupPlaces : $req->places;
@endphp
@component('mail::message')
# {{ $isTopup ? 'คำขออนุมัติเพิ่มงบประมาณถูกส่งกลับให้แก้ไข' : 'คำขออนุมัติสถานที่ถูกส่งกลับให้แก้ไข' }}

เรียน คุณ {{ optional($req->requester)->full_name ?: (optional($req->requester)->name ?? 'ผู้ขออนุมัติ') }},

{{ $isTopup ? 'คำขออนุมัติเพิ่มงบประมาณ' : 'คำขออนุมัติสถานที่/ค่าใช้จ่ายกิจกรรมการตลาด' }} (จำนวน {{ $lines->count() }} รายการ ประจำเดือน {{ $req->period ?? '-' }})
ถูกส่งกลับโดย คุณ {{ optional($req->approver)->full_name ?: (optional($req->approver)->name ?? 'ผู้อนุมัติ') }} เพื่อให้แก้ไข

@if (!empty($reason))
**สิ่งที่ต้องแก้ไข:**
{{ $reason }}
@endif

กรุณาแก้ไขข้อมูลสถานที่ในระบบ แล้วส่งขออนุมัติใหม่อีกครั้ง

@component('mail::button', ['url' => $settingsUrl])
ไปที่หน้าตั้งค่าแหล่งที่มา
@endcomponent

ขอบคุณครับ
@endcomponent
