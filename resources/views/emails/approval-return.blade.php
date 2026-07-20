@php
    $sc        = $saleCar;
    $brandName = config("brand.names.{$sc->brand}") ?? ('Brand ' . ($sc->brand ?? '-'));
    $custName  = trim(
        ($sc->customer->prefix->Name_TH ?? '') . ' ' .
        ($sc->customer->FirstName ?? '') . ' ' .
        ($sc->customer->LastName ?? '')
    );
@endphp
@component('mail::message')
# 🔁 ตีกลับใบจอง

**แบรนด์: {{ $brandName }}**

คำขออนุมัติใบจองนี้ถูก **ตีกลับโดย {{ $returnedBy }}** กรุณาตรวจสอบและแก้ไขข้อมูล

> **เหตุผล:** {{ $reason ?: '-' }}

---

### ข้อมูลใบจอง
- **ลูกค้า :** {{ $custName ?: '-' }}
- **รุ่นรถ :** {{ $sc->model->Name_TH ?? '-' }} / {{ $sc->subModel->name ?? '-' }}
- **ฝ่ายขาย :** {{ $sc->saleUser->name ?? '-' }}
- **วันที่จอง :** {{ $sc->BookingDate ? \Illuminate\Support\Carbon::parse($sc->BookingDate)->format('d/m/Y') : '-' }}

---

@if ($actionUrl)
กรุณากดปุ่มด้านล่างเพื่อดำเนินการต่อ

@component('mail::button', ['url' => $actionUrl])
เปิดหน้าอนุมัติ
@endcomponent
@else
ลายเซ็นอนุมัติทั้งหมดถูกรีเซ็ตแล้ว — กรุณาแก้ไขข้อมูลในระบบ แล้ว **ส่งขออนุมัติใหม่** อีกครั้ง
@endif

ขอบคุณครับ
@endcomponent
