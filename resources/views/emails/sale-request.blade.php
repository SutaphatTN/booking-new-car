@php $__brandName = config("brand.names.{$saleCar->brand}") ?? ('Brand ' . ($saleCar->brand ?? '-')); @endphp
@component('mail::message')
# แจ้งเตือนการขออนุมัติ

**แบรนด์: {{ $__brandName }}**

มีรายการขออนุมัติจาก {{ $saleCar->saleUser->name }}

@php $__case = $saleCar->approvalCase(); @endphp
### ประเภทคำขอ
@if ($type === 'normal')
🔵 **ขออนุมัติยอดปกติ**
@elseif ($type === 'manager_revise')
🔁 **ผู้อนุมัติตีกลับ — ขอให้ผู้จัดการกรอกค่าคอมฝ่ายขายที่ได้ใหม่**
@if (!empty($saleCar->approval_md_note))

> **โน้ตจากผู้อนุมัติ :** {{ $saleCar->approval_md_note }}
@endif
@elseif ($type === 'gm_final')
🟢 **ขออนุมัติเกินงบ (เกินเพดาน) — เสนอ GM อนุมัติขั้นสุดท้าย**

🔺 *ผู้จัดการกรอกค่าคอมฝ่ายขายที่ได้แล้ว — GM ตรวจ/แก้ยอด แล้วอนุมัติ (MD รับทราบผ่านสำเนา ไม่ต้องกดอนุมัติ)*
@elseif ($type === 'md_final')
🟠 **ขออนุมัติเกินงบ — เสนอ MD (ขั้นสุดท้าย)**
@elseif ($type === 'gm')
🟠 **ขออนุมัติเกินงบ — GM พิจารณา**

🔺 *เลือก: หักค่าคอมฝ่ายขายแล้วอนุมัติจบ หรือ ส่งต่อให้ MD อนุมัติขั้นสุดท้าย*
@elseif ($__case === 'b1_md')
🔴 **ขออนุมัติเกินงบ (เกินเพดาน)**

🔺 *เกินเพดานอนุมัติของผู้จัดการ — กรุณากรอกค่าคอมฝ่ายขายที่ได้ จากนั้นระบบจะส่งต่อให้ GM อนุมัติขั้นสุดท้าย*
@else
🔴 **ขออนุมัติเกินงบ (ไม่เกินเพดาน)**

🟢 *อยู่ในเพดานอนุมัติของผู้จัดการ — ผู้จัดการอนุมัติได้เลย*
@endif

---

@php $__brand = (int) $saleCar->brand; @endphp
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
- **Com Finance :** {{ number_format($data['com_fin'], 2) }}
- **ยอดรวมแคมเปญ :** **{{ number_format($data['campaign_total'], 2) }}**

@if (!empty($data['campaign_details']) && count($data['campaign_details']))
**รายละเอียด RI (cashSupport)**
@component('mail::table')
| แคมเปญ | จำนวนเงิน |
| :----- | --------: |
@foreach ($data['campaign_details'] as $c)
| {{ str_replace('|', '/', $c['name']) }} | {{ number_format($c['amount'], 2) }} |
@endforeach
@endcomponent
@endif

### รายการหัก
- **ของแถม (ราคาทุนอะไหล่) :** {{ number_format($data['gift_total'], 2) }}
- **ส่วนลด :** {{ number_format($data['discount'], 2) }}

@if (!empty($data['gift_details']) && count($data['gift_details']))
**รายละเอียดของแถม (ราคาทุนอะไหล่)**
@component('mail::table')
| รายการ | ราคาทุนอะไหล่ |
| :----- | -----------: |
@foreach ($data['gift_details'] as $g)
| {{ str_replace('|', '/', $g['detail']) }} | {{ number_format($g['amount'], 2) }} |
@endforeach
@endcomponent
@endif

### ยอดที่เหลือ
<span style="color: {{ ($data['remaining'] ?? 0) < 0 ? '#dc2626' : '#059669' }}; font-weight:bold; font-size:1.15em;">{{ number_format($data['remaining'], 2) }}</span>

@isset($data['commission_deduct'])
### สรุปจากผู้จัดการ
- **ค่าคอมฝ่ายขายที่ได้ :** {{ number_format($data['commission_deduct'], 2) }}
@if(($data['extra_budget'] ?? null) !== null)
- **เก็บงบเพิ่มเติม :** **{{ number_format($data['extra_budget'], 2) }}**
@endif
@endisset
@endisset

---

@if(!empty($saleCar->approval_token))
@component('mail::button', ['url' => route('purchase-order.emailApprove', $saleCar->approval_token), 'color' => 'success'])
อนุมัติ
@endcomponent

{{-- ปุ่ม "ดูรายละเอียด" ปิดไว้ก่อน (uncomment เพื่อเปิดใช้ — ชี้ PDF สรุปการขาย read-only ผ่าน token)
@component('mail::button', ['url' => route('purchase-order.emailSummary', $saleCar->approval_token), 'color' => 'primary'])
ดูรายละเอียด
@endcomponent
--}}
@endif


ขอบคุณครับ
@endcomponent