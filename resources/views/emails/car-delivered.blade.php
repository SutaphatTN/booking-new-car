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
    // ชื่อไฟแนนซ์: non-finance = ซื้อสด, finance = ชื่อบริษัทไฟแนนซ์ (ถ้าไม่ได้เลือก = -)
    $financeName = $sc->payment_mode === 'finance'
        ? ($sc->remainingPayment?->financeInfo?->FinanceCompany ?? '-')
        : 'ซื้อสด';
@endphp
@component('mail::message')
# 🚗 แจ้งส่งมอบรถ

**แบรนด์: {{ $brandName }}**

รายการจองนี้มี **ข้อมูลการส่งมอบ** แล้ว — กรุณาดำเนินการจบยอดที่ธนาคาร

@if (!empty($triggers))
> **แจ้งเตือนจากข้อมูล:** {{ implode(', ', $triggers) }}
@endif

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
- **ไฟแนนซ์ :** {{ $financeName }}
- **สถานะ :** {{ $sc->conStatus->name ?? '-' }}
- **วันส่งมอบจริง (แจ้งประกัน) :** {{ $sc->DeliveryDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryDate)->format('d/m/Y') : '-' }}
- **วันส่งมอบของบริษัท (DMS) :** {{ $sc->DeliveryInDMSDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryInDMSDate)->format('d/m/Y') : '-' }}
- **วันส่งมอบของฝ่ายขาย (CK) :** {{ $sc->DeliveryInCKDate ? \Illuminate\Support\Carbon::parse($sc->DeliveryInCKDate)->format('d/m/Y') : '-' }}
- **ฝ่ายขาย :** {{ $sc->saleUser->name ?? '-' }}
- **สาขา :** {{ $sc->saleUser->branchInfo->name ?? '-' }}

---

รบกวนดำเนินการจบยอดที่ธนาคารต่อไป ขอบคุณครับ
@endcomponent
