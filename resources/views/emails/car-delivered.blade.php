@php
    $sc        = $saleCar;
    $brand     = (int) $sc->brand;
    $brandName = config("brand.names.{$sc->brand}") ?? ('Brand ' . ($sc->brand ?? '-'));
    $co        = $sc->carOrder;
    $custName  = trim(
        ($sc->customer->prefix->Name_TH ?? '') . ' ' .
        ($sc->customer->FirstName ?? '') . ' ' .
        ($sc->customer->LastName ?? '')
    );
    $color = in_array($brand, [2, 3, 4]) ? ($sc->gwmColor->name ?? '-') : ($sc->Color ?? '-');
@endphp
@component('mail::message')
# 🚗 แจ้งส่งมอบรถ

**แบรนด์: {{ $brandName }}**

รายการจองนี้ได้ **ส่งมอบรถให้ลูกค้าเรียบร้อยแล้ว** — กรุณาดำเนินการจบยอดที่ธนาคาร

---

### ข้อมูลลูกค้า
- **ชื่อ-สกุล :** {{ $custName ?: '-' }}
- **เลขบัตรประชาชน :** {{ $sc->customer->IDNumber ?? '-' }}
- **เบอร์โทร :** {{ $sc->customer->Mobilephone1 ?? '-' }}

### ข้อมูลรถ
- **รุ่นรถหลัก :** {{ $sc->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $sc->subModel->name ?? '-' }}
- **สี :** {{ $color }}
@if ($brand == 2)
- **สีภายใน :** {{ $sc->interiorColor->name ?? '-' }}
@endif
- **ปี :** {{ $sc->Year ?? '-' }}
@if ($brand == 1)
- **Option :** {{ $sc->option ?? '-' }}
@endif
- **เลขตัวถัง (VIN) :** {{ $co->vin_number ?? '-' }}
- **เลขเครื่องยนต์ :** {{ $co->engine_number ?? '-' }}

### การส่งมอบ
- **วันที่ส่งมอบ :** {{ $sc->DeliveryDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryDate)->format('d/m/Y') : '-' }}
- **ฝ่ายขาย :** {{ $sc->saleUser->name ?? '-' }}
- **สาขา :** {{ $sc->saleUser->branchInfo->name ?? '-' }}

---

รบกวนดำเนินการจบยอดที่ธนาคารต่อไป ขอบคุณครับ
@endcomponent
