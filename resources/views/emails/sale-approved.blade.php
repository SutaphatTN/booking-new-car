@php
    $__brand = (int) $saleCar->brand;
    $__brandName = config("brand.names.{$__brand}") ?? ('Brand ' . ($__brand ?: '-'));
@endphp
@component('mail::message')
# ใบจองได้รับการอนุมัติแล้ว

**แบรนด์: {{ $__brandName }}**

ใบจองของ **{{ $saleCar->saleUser->name ?? '-' }}** ได้รับการอนุมัติเรียบร้อยแล้ว

---

### ข้อมูลรถ
- **ลูกค้า :** {{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}
- **รุ่นรถหลัก :** {{ $saleCar->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $saleCar->subModel->name ?? '-' }}
- **สี :** {{ in_array($__brand, [2, 3, 4]) ? ($saleCar->gwmColor->name ?? '-') : ($saleCar->Color ?? '-') }}
@if ($__brand == 2)
- **สีภายใน :** {{ $saleCar->interiorColor->name ?? '-' }}
@endif
- **ปี :** {{ $saleCar->Year ?? '-' }}
@if ($__brand == 1)
- **Option :** {{ $saleCar->option ?? '-' }}
@endif

---

{{-- ลิงก์ผ่าน token (unscoped) เปิดได้ทุก brand — เดิมชี้ /edit/{id} ที่ scoped ทำให้ 404 ข้าม brand --}}
@if(!empty($saleCar->approval_token))
@component('mail::button', ['url' => route('purchase-order.emailApprove', $saleCar->approval_token)])
ดูรายละเอียด
@endcomponent
@endif

ขอแสดงความนับถือ
@endcomponent
