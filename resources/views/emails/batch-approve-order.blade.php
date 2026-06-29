@component('mail::message')
# แจ้งเตือนคำขอสั่งซื้อรถ

เรียน คุณ {{ $approverName }}

มีคำขอสั่งซื้อรถรออนุมัติทั้งหมด **{{ count($items) }}** รายการ ดังนี้

@component('mail::table')
| # | รหัส | ประเภท | รุ่นรถหลัก | รุ่นรถย่อย | สี | ปี | จำนวน |
|:-:|:--|:--|:--|:--|:--|:-:|:-:|
@foreach ($items as $i => $it)
| {{ $i + 1 }} | {{ $it['order_code'] }} | {{ $it['type'] }} | {{ $it['model'] }} | {{ $it['subModel'] }} | {{ $it['color'] }} | {{ $it['year'] }} | {{ $it['qty'] }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('car-order.process')])
ดูรายละเอียด / อนุมัติ
@endcomponent

ขอแสดงความนับถือ
@endcomponent
