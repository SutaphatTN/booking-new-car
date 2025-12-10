@component('mail::message')
# แจ้งเตือนคำขอสั่งซื้อรถ

เรียน คุณ {{ $order->approvers->name ?? 'ผู้อนุมัติ' }}

---

### ข้อมูลรถ
- **รหัส Car Order :** {{ $order->order_code ?? '-' }}
- **รุ่นรถหลัก :** {{ $order->model->Name_TH ?? '-' }}
- **รุ่นรถย่อย :** {{ $order->subModel->name ?? '-' }}
- **หมายเหตุ :** {{ $order->note ?? '-' }}

---

@component('mail::button', ['url' => route('car-order.process')])
ดูรายละเอียด
@endcomponent


ขอแสดงความนับถือ
@endcomponent