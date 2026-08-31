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
🔁 **ผู้อนุมัติตีกลับ — ขอให้ผู้จัดการทบทวนยอดที่ต้องหักใหม่**
@if (!empty($saleCar->approval_md_note))

> **โน้ตจากผู้อนุมัติ :** {{ $saleCar->approval_md_note }}
@endif
@elseif ($type === 'gm_final')
🟢 **ขออนุมัติเกินงบ (เกินเพดาน) — เสนอ GM อนุมัติขั้นสุดท้าย**

🔺 *ผู้จัดการกรอกยอดที่ต้องหักแล้ว — GM ตรวจ/แก้ยอด แล้วอนุมัติ (MD รับทราบผ่านสำเนา ไม่ต้องกดอนุมัติ แต่กดตีกลับได้)*
@elseif ($type === 'md_final')
🟠 **ขออนุมัติเกินงบ — เสนอ MD อนุมัติขั้นสุดท้าย**
@if ($saleCar->isVipApproval())

🔺 *ผู้จัดการเลือก "ไม่หักเงิน (VIP)" — MD ตรวจแล้วอนุมัติ (GM รับทราบผ่านสำเนา)*
@endif
@elseif ($__case === 'b1_md' || $__case === 'b2_gm')
🔴 **ขออนุมัติเกินงบ (เกินเพดาน)**

{{-- ใช้คำคงที่ ไม่เรียก approvalDeductLabel() : ตอนส่งเมลฉบับแรก ธง approval_is_deduct ยังเป็น 0
     (ผู้จัดการยังไม่กรอก) เมธอดนั้นจะตกไปใช้กติกาแบรนด์เดิม แล้วขึ้นคำผิดว่า "ค่าคอมฝ่ายขายที่ได้"
     เมลทุกฉบับเป็นรอบอนุมัติที่ยังไม่จบ = ใช้กติกาใหม่เสมอ --}}
🔺 *เกินเพดานอนุมัติของผู้จัดการ — กรุณากรอกยอดที่ต้องหัก (ระบบเติมยอด {{ \App\Models\Salecar::OVER_BUDGET_DEDUCT_PERCENT }}% ของเกินงบให้แล้ว แก้เพิ่มได้) จากนั้นระบบจะส่งต่อให้ GM อนุมัติขั้นสุดท้าย*
@if ($saleCar->allowsVipChoice())

🔺 *ลูกค้า VIP เลือก "ไม่หักเงิน" ได้ — ระบบจะส่งให้ MD อนุมัติแทน (สำเนาถึง GM)*
@endif
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
@if (\App\Support\BrandFeature::hasInteriorColor($__brand))
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
@if ($data['has_margin'] ?? true)
- **Margin (2%) :** {{ number_format($data['margin'], 2) }}
@endif
- **RI (cashSupport) :** {{ number_format($data['ri'], 2) }}
- **Com Finance :** {{ number_format($data['com_fin'], 2) }}
@if (!empty($data['is_finance']))
- **บวกหัว (90%) :** {{ number_format($data['markup90'] ?? 0, 2) }}
@endif
- **ลูกค้าจ่ายเพิ่ม :** {{ number_format($data['customer_extra'] ?? 0, 2) }}
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
@if (!empty($data['is_finance']))
- **ส่วนลดเงินดาวน์ :** {{ number_format($data['down_payment_discount'] ?? 0, 2) }}
- **Vat ของแถม :** {{ number_format($data['accessory_gift_vat'] ?? 0, 2) }}
@endif

@if (!empty($data['gift_details']) && count($data['gift_details']))
**รายละเอียดของแถม (ราคาทุนอะไหล่)**
@component('mail::table')
| รายการ | ราคาทุนอะไหล่ |
| :----- | -----------: |
@foreach ($data['gift_details'] as $g)
| {{ str_replace('|', '/', $g['detail']) }}{!! filled($g['note'] ?? null)
    ? '<br><span style="color:#6b7280; font-size:12px;">หมายเหตุ: ' . e(str_replace('|', '/', $g['note'])) . '</span>'
    : '' !!} | {{ number_format($g['amount'], 2) }} |
@endforeach
@endcomponent
@endif

### ยอดที่เหลือ
<span style="color: {{ ($data['remaining'] ?? 0) < 0 ? '#dc2626' : '#059669' }}; font-weight:bold; font-size:1.15em;">{{ number_format($data['remaining'], 2) }}</span>

@php
    // สรุปยอด : 2 บรรทัดแรกแสดงทุกฉบับตั้งแต่เมลแรก
    //   · Margin คงเหลือ = ยอดที่เหลือ (ตัวเดียวกับหัวข้อด้านบน)
    //   · สรุปการแถม     = balanceCampaign × 2 (ยอดเต็ม) → บอกว่า "งบเหลือ" หรือ "เกินงบ"
    // อีก 2 บรรทัดโผล่เมื่อผู้จัดการตกลงยอดหักแล้ว (อ่านจาก $data ก่อน ไม่มีค่อยดูค่าที่บันทึกไว้ในใบ)
    //   · เปอร์เซ็นต์หัก  = คิดย้อนจากยอดจริงที่กรอก → ปกติ 10% แต่ถ้าแก้ยอด % ขยับตาม
    //   · สรุปหักค่าคอม  = ยอดที่ผู้จัดการตกลง
    $__balFull = (float) ($saleCar->balanceCampaign ?? 0) * 2;
    $__isOver  = $__balFull < 0;
    $__deduct  = $data['commission_deduct'] ?? $saleCar->approval_commission_deduct;
    $__pct     = ($__deduct !== null && $__isOver && abs($__balFull) > 0)
        ? abs((float) $__deduct) / abs($__balFull) * 100
        : null;
@endphp

### สรุปยอด
- **Margin คงเหลือ :** {{ number_format($data['remaining'], 2) }}
- **สรุปการแถม :** {{ $__isOver ? 'เกินงบ' : 'งบเหลือ' }} {{ number_format($__balFull, 2) }}
@if ($__deduct !== null)
- **เปอร์เซ็นต์หัก :** {{ $__pct !== null ? number_format($__pct, 2) . '%' : '-' }}
- **สรุปหักค่าคอม :** **{{ number_format((float) $__deduct, 2) }}**
@endif

@if (($data['commission_deduct'] ?? null) !== null && ($saleCar->isVipApproval() || ($data['extra_budget'] ?? null) !== null))
### สรุปจากผู้จัดการ
@if ($saleCar->isVipApproval())
- **การตัดสินใจ :** ไม่หักเงิน (VIP)
@endif
@if(($data['extra_budget'] ?? null) !== null)
- **เก็บงบเพิ่มเติม :** **{{ number_format($data['extra_budget'], 2) }}**
@endif
@endif
@endisset

---

@php
    // ลิงก์ของเมลฉบับนี้ — ฉบับผู้อนุมัติตัวจริงใช้ token เฉพาะ ส่วนสำเนา (CC) ใช้ token ปกติ
    $mailLinkToken = ($linkToken ?? null) ?: $saleCar->approval_token;
@endphp
@if(!empty($mailLinkToken))
@if(!empty($isCopy))
> **สำเนาเพื่อรับทราบ** — ปุ่มด้านล่างเปิดดูรายละเอียดและตีกลับได้ แต่กดอนุมัติขั้นสุดท้ายไม่ได้
@endif
@component('mail::button', ['url' => route('purchase-order.emailApprove', $mailLinkToken), 'color' => empty($isCopy) ? 'success' : 'primary'])
{{ empty($isCopy) ? 'อนุมัติ' : 'เปิดดูรายละเอียด' }}
@endcomponent

{{-- ปุ่ม "ดูรายละเอียด" ปิดไว้ก่อน (uncomment เพื่อเปิดใช้ — ชี้ PDF สรุปการขาย read-only ผ่าน token)
@component('mail::button', ['url' => route('purchase-order.emailSummary', $mailLinkToken), 'color' => 'primary'])
ดูรายละเอียด
@endcomponent
--}}
@endif


ขอบคุณครับ
@endcomponent