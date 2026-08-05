@php $brandName = config("brand.names.{$brand}") ?? ('Brand ' . ($brand ?? '-')); @endphp
@component('mail::message')
# แจ้งเตือนคำขอสั่งซื้อรถ

**แบรนด์: {{ $brandName }}**

เรียน คุณ {{ $approverName }}

มีคำขอสั่งซื้อรถรออนุมัติทั้งหมด **{{ count($items) }}** รายการ ดังนี้

{{-- แหล่งที่มา: เคส OTHDealer ขึ้นบรรทัดที่สองบอกจังหวัด · ชื่อดีลเลอร์ (ชื่อไม่บังคับกรอก อาจไม่มี) --}}
@component('mail::table')
| # | รหัส | ประเภท | แหล่งที่มา | รุ่นรถหลัก | รุ่นรถย่อย | สี | ปี | จำนวน |
|:-:|:--|:--|:--|:--|:--|:--|:-:|:-:|
@foreach ($items as $i => $it)
| {{ $i + 1 }} | {{ $it['order_code'] }} | {{ $it['type'] }} | {{ $it['source'] }}{!! $it['dealer'] ? '<br><small>' . e($it['dealer']) . '</small>' : '' !!} | {{ $it['model'] }} | {{ $it['subModel'] }} | {{ $it['color'] }} | {{ $it['year'] }} | {{ $it['qty'] }} |
@endforeach
@endcomponent

{{-- พ่วง brand ของคำขอไปด้วย — ผู้อนุมัติที่กำลังอยู่คนละ brand จะถูกสลับให้ตรงก่อนเข้าหน้า --}}
@component('mail::button', ['url' => route('car-order.process', array_filter(['brand' => $brand]))])
ดูรายละเอียด / อนุมัติ
@endcomponent

ขอแสดงความนับถือ
@endcomponent
