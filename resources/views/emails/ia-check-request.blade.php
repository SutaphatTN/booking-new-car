@php
  $__brand = (int) $saleCar->brand;
  $__brandName = config("brand.names.{$__brand}") ?? ('Brand ' . ($saleCar->brand ?? '-'));
@endphp
@component('mail::message')
# ขอให้ตรวจสอบรายการ (IA)

**แบรนด์: {{ $__brandName }}**

@if ($requestedBy)
มีคำขอให้ตรวจสอบรายการจาก **{{ $requestedBy }}**
@else
มีคำขอให้ตรวจสอบรายการใบจอง
@endif

@if ($saleCar->needsIaCheck())
🔺 *ใบจองนี้ต้องผ่านการติ๊ก "ตรวจสอบรายการ (IA)" ก่อน จึงจะเปลี่ยนสถานะเป็น "ส่งมอบ" ได้*
@endif

---

### ข้อมูลใบจอง
- **เลขที่ใบจอง :** #{{ $saleCar->id }}
- **ลูกค้า :** {{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}
- **ฝ่ายขาย :** {{ $saleCar->saleUser->name ?? '-' }}
- **รุ่นรถหลัก :** {{ $saleCar->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $saleCar->subModel->name ?? '-' }}
- **สี :** {{ in_array($__brand, [2, 3, 4]) ? ($saleCar->gwmColor->name ?? '-') : ($saleCar->Color ?? '-') }}
- **ปี :** {{ $saleCar->Year ?? '-' }}
- **วันที่จอง :** {{ $saleCar->BookingDate ?? '-' }}
- **สถานะปัจจุบัน :** {{ $saleCar->conStatus->name ?? '-' }}

---

@component('mail::button', ['url' => route('purchase-order.iaReview', $token), 'color' => 'primary'])
เปิดใบจองเพื่อตรวจสอบ
@endcomponent

*ลิงก์จะพาไปที่หน้าใบจองใบนี้โดยตรง และสลับแบรนด์ให้อัตโนมัติหากกำลังใช้งานอยู่คนละแบรนด์*

@endcomponent
