@component('mail::message')
# แจ้งเตือนการขออนุมัติ

มีรายการขออนุมัติจาก {{ $saleCar->saleUser->name }}

### ประเภทคำขอ
@if ($type === 'normal')
🔵 **ขออนุมัติยอดปกติ**
@else
🔴 **ขออนุมัติเกินงบ**
@endif

---

### ข้อมูลรถ
- **ลูกค้า :** {{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}
- **รุ่นรถหลัก :** {{ $saleCar->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $saleCar->subModel->name ?? '-' }}
- **สี :** {{ $saleCar->Color ?? '-' }}
- **ปี :** {{ $saleCar->Year ?? '-' }}
- **Option :** {{ $saleCar->option ?? '-' }}
- **ยอดคงเหลือแคมเปญ :**
{{
    $saleCar->balanceCampaign !== null 
        ? number_format(max(0, $saleCar->balanceCampaign), 2) 
        : '' 
}}
@if(!empty($saleCar->reason_campaign))
- **สาเหตุที่งบเกิน :** {{ $saleCar->reason_campaign }}
@endif

---

@component('mail::button', ['url' => url('/purchase-order/'.$saleCar->id.'/edit')])
ดูรายละเอียด
@endcomponent


ขอบคุณครับ
@endcomponent