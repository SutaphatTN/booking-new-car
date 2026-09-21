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
# 🔄 แจ้งเปลี่ยนคันรถ

**แบรนด์: {{ $brandName }}**

ใบจองนี้เคยแจ้งส่งมอบไปแล้ว แต่ **เปลี่ยนคันรถภายหลัง** — รถคันเดิม **ไม่ได้ส่งมอบ** ให้ลูกค้ารายนี้

@if ($newCarOrder)
กรุณายกเลิก/แก้ไขการจบยอดที่ธนาคารให้ตรงกับ **VIN คันใหม่** ด้านล่าง
@else
ตอนนี้ใบจอง **ยังไม่ได้ผูกคันใหม่** กรุณาระงับการจบยอดของ VIN เดิมไว้ก่อน — ระบบจะแจ้งอีกครั้งเมื่อผูกคันใหม่แล้ว
@endif

---

### ข้อมูลลูกค้า
- **ชื่อ-สกุล :** {{ $custName ?: '-' }}
- **เลขบัตรประชาชน :** {{ $sc->customer->IDNumber ?? '-' }}
- **เบอร์โทร :** {{ $sc->customer->Mobilephone1 ?? '-' }}

### ❌ คันเดิม (ยกเลิก)
- **เลขตัวถัง (VIN) :** {{ $oldCarOrder->vin_number ?? '-' }}
- **เลขเครื่องยนต์ :** {{ $oldCarOrder->engine_number ?? '-' }}
- **รุ่นรถ :** {{ $oldCarOrder->model->Name_TH ?? '-' }} {{ $oldCarOrder->subModel->name ?? '' }}

### ✅ คันใหม่
@if ($newCarOrder)
- **เลขตัวถัง (VIN) :** {{ $newCarOrder->vin_number ?? '-' }}
- **เลขเครื่องยนต์ :** {{ $newCarOrder->engine_number ?? '-' }}
- **รุ่นรถ :** {{ $newCarOrder->model->Name_TH ?? '-' }} {{ $newCarOrder->subModel->name ?? '' }}
@else
- **ยังไม่ผูกคันใหม่**
@endif

### การส่งมอบ (ข้อมูลล่าสุดของใบจอง)
- **สถานะ :** {{ $sc->conStatus->name ?? '-' }}
- **วันส่งมอบจริง (แจ้งประกัน) :** {{ $sc->DeliveryDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryDate)->format('d/m/Y') : '-' }}
- **วันส่งมอบของบริษัท (DMS) :** {{ $sc->DeliveryInDMSDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryInDMSDate)->format('d/m/Y') : '-' }}
- **วันส่งมอบของฝ่ายขาย (CK) :** {{ $sc->DeliveryInCKDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryInCKDate)->format('d/m/Y') : '-' }}
- **ฝ่ายขาย :** {{ $sc->saleUser->name ?? '-' }}
- **สาขา :** {{ $sc->saleUser->branchInfo->name ?? '-' }}

---

ขออภัยในความไม่สะดวก ขอบคุณครับ
@endcomponent
