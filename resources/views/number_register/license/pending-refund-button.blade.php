{{-- ปุ่มของหน้า "ค้างคืนเงินป้ายแดง" — ป้ายคืนไปแล้ว เหลือแค่เรื่องเงิน
     จึงมีแค่ แก้ไขข้อมูล + ยืนยันการจ่ายเงินจริง (ใช้โมดัลและ endpoint ตัวเดียวกับหน้าป้ายแดง) --}}
@php
  $viewOnly = auth()->user()->isRegistrationViewOnly();
  $canFinance = !$viewOnly
      && in_array(auth()->user()->role, App\Http\Controllers\vehicle\LicenseController::FINANCE_ROLES, true);
  $approveMissing = $history->approveFinanceMissing();
@endphp

<button class="btn btn-icon btn-info btnViewLicense" data-id="{{ $history->id }}" title="ดูข้อมูล">
  <i class="bx bx-show"></i>
</button>

@unless ($viewOnly)
  <button class="btn btn-icon btn-warning btnEditLicense" data-id="{{ $history->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
  </button>
@endunless

@if ($canFinance)
  <button class="btn btn-icon btn-success btnApproveFinance" data-id="{{ $history->id }}"
    data-missing="{{ implode(', ', $approveMissing) }}"
    title="{{ $approveMissing ? 'ยังกรอกข้อมูลไม่ครบ : ' . implode(', ', $approveMissing) : 'ยืนยันการจ่ายเงินจริง (ปิดรายการ)' }}">
    <i class="bx bx-check"></i>
  </button>
@endif
