@php
    $brand = $order->brand;

    // สี — brand 2/3 ใช้ gwmColor, นอกนั้นใช้ฟิลด์ color
    $colorName = in_array($brand, [2, 3, 4])
        ? ($order->gwmColor->name ?? '-')
        : ($order->color ?? '-');

    // สีภายใน — เฉพาะ brand = 2
    $interiorColorName = $brand == 2 ? ($order->interiorColor->name ?? '-') : null;

    // รุ่นรถย่อย — ใช้ detail เป็นหลัก ถ้ามี name ก็ดึงมาต่อท้าย
    $subDetail = $order->subModel->detail ?? null;
    $subName   = $order->subModel->name ?? null;
    $subModelText = $subDetail && $subName
        ? "{$subDetail} - {$subName}"
        : ($subDetail ?: ($subName ?: '-'));

    $brandName = config("brand.names.{$brand}") ?? ('Brand ' . ($brand ?? '-'));
@endphp

@component('mail::message')
# แจ้งเตือนคำขอสั่งซื้อรถ

**แบรนด์: {{ $brandName }}**

เรียน คุณ {{ $order->approvers->name ?? 'ผู้อนุมัติ' }}

---

### ข้อมูลรถ
- **รหัส Car Order :** {{ $order->order_code ?? '-' }}
- **รุ่นรถหลัก :** {{ $order->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $subModelText }}
- **สี :** {{ $colorName }}
@if ($brand == 1)
- **Option :** {{ $order->option ?? '-' }}
@endif
- **ปี :** {{ $order->year ?? '-' }}
@if ($brand == 2)
- **สีภายใน :** {{ $interiorColorName }}
@endif
- **หมายเหตุ :** {{ $order->note ?? '-' }}

---

{{-- พ่วง brand ของคำขอไปด้วย — ผู้อนุมัติที่กำลังอยู่คนละ brand จะถูกสลับให้ตรงก่อนเข้าหน้า --}}
@component('mail::button', ['url' => route('car-order.process', array_filter(['brand' => $brand]))])
ดูรายละเอียด
@endcomponent


ขอแสดงความนับถือ
@endcomponent
