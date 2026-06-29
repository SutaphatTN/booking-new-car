@php $__brand = (int) $saleCar->brand; @endphp
@component('mail::message')
# ใบจองได้รับการอนุมัติแล้ว

ใบจองของ **{{ $saleCar->saleUser->name ?? '-' }}** ได้รับการอนุมัติเรียบร้อยแล้ว

---

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

---

@component('mail::button', ['url' => url('/purchase-order/'.$saleCar->id.'/edit')])
ดูรายละเอียด
@endcomponent

ขอแสดงความนับถือ
@endcomponent
