@php $__brandName = config("brand.names.{$saleCar->brand}") ?? ('Brand ' . ($saleCar->brand ?? '-')); @endphp
@component('mail::message')
# แจ้งเตือนการขออนุมัติ

**แบรนด์: {{ $__brandName }}**

มีรายการขออนุมัติจาก {{ $saleCar->saleUser->name }}

### ประเภทคำขอ
@if ($type === 'normal')
🔵 **ขออนุมัติยอดปกติ**
@else
🔴 **ขออนุมัติเกินงบ**
@endif

---

@php $__brand = (int) $saleCar->brand; @endphp
### ข้อมูลรถ
- **ลูกค้า :** {{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}
- **รุ่นรถหลัก :** {{ $saleCar->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $saleCar->subModel->name ?? '-' }}
- **สี :** {{ in_array($__brand, [2, 3]) ? ($saleCar->gwmColor->name ?? '-') : ($saleCar->Color ?? '-') }}
@if ($__brand == 2)
- **สีภายใน :** {{ $saleCar->interiorColor->name ?? '-' }}
@endif
- **ปี :** {{ $saleCar->Year ?? '-' }}
@if ($__brand == 1)
- **Option :** {{ $saleCar->option ?? '-' }}
@endif
- **ยอดคงเหลือแคมเปญ :**
{{
    $saleCar->balanceCampaign !== null 
        ? number_format(max(0, $saleCar->balanceCampaign), 2) 
        : '' 
}}
@if(!empty($saleCar->reason_campaign))
- **สาเหตุที่งบเกิน :** {{ $saleCar->reason_campaign }}
@endif

@isset($data)
---

### สรุปยอดแคมเปญ
- **ราคาขาย :** {{ number_format($data['price_sub'], 2) }}
- **Margin (2%) :** {{ number_format($data['margin'], 2) }}
- **RI (cashSupport) :** {{ number_format($data['ri'], 2) }}
@forelse ($data['campaign_details'] as $c)
&nbsp;&nbsp;• {{ $c['name'] }} — {{ number_format($c['amount'], 2) }}
@empty
&nbsp;&nbsp;• ไม่มีแคมเปญ
@endforelse
- **Com Finance :** {{ number_format($data['com_fin'], 2) }}
- **ยอดรวมแคมเปญ :** **{{ number_format($data['campaign_total'], 2) }}**

### รายการหัก
- **ของแถม (ราคาทุนอะไหล่) :** {{ number_format($data['gift_total'], 2) }}
@forelse ($data['gift_details'] as $g)
&nbsp;&nbsp;• {{ $g['detail'] }} — {{ number_format($g['amount'], 2) }}
@empty
&nbsp;&nbsp;• ไม่มีของแถม
@endforelse
- **ส่วนลด :** {{ number_format($data['discount'], 2) }}

### ยอดที่เหลือ
**{{ number_format($data['remaining'], 2) }}**

@isset($data['commission_deduct'])
### สรุปจากผู้จัดการ
- **หักค่าคอมฝ่ายขาย :** {{ number_format($data['commission_deduct'], 2) }}
- **เก็บงบเพิ่มเติม (ยอดที่เหลือ − หักค่าคอม) :** **{{ number_format($data['extra_budget'] ?? 0, 2) }}**
@endisset
@endisset

---

@if(!empty($saleCar->approval_token))
@component('mail::button', ['url' => route('purchase-order.emailApprove', $saleCar->approval_token), 'color' => 'success'])
อนุมัติ
@endcomponent

{{-- ลิงก์ผ่าน token (unscoped) เปิดได้ทุก brand — เดิมชี้ /edit/{id} ที่ scoped ทำให้ 404 ข้าม brand --}}
@component('mail::button', ['url' => route('purchase-order.emailApprove', $saleCar->approval_token), 'color' => 'primary'])
ดูรายละเอียด
@endcomponent
@endif


ขอบคุณครับ
@endcomponent