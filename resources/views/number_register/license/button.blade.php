@php
  use App\Http\Controllers\vehicle\LicenseController;

  $user = auth()->user();
  // role ดูอย่างเดียว (insurance_reg) — เห็นแต่ปุ่มดูข้อมูล ไม่มีปุ่มแก้ไข/ยืนยันการจ่ายเงิน
  $viewOnly = $user->isRegistrationViewOnly();
  // ปิดเงิน กับ คืนป้ายก่อน คนละสิทธิ์กัน — md คืนป้ายได้แต่ปิดเงินไม่ได้
  $canFinance = !$viewOnly && in_array($user->role, LicenseController::FINANCE_ROLES, true);
  $canReturnEarly = !$viewOnly && in_array($user->role, LicenseController::RETURN_PLATE_EARLY_ROLES, true);
@endphp

@if ($history)
  <button class="btn btn-icon btn-info btnViewLicense" data-id="{{ $history?->id }}" title="ดูข้อมูล"
    {{ $history ? '' : 'disabled' }}>
    <i class="bx bx-show"></i>
  </button>
  @unless ($viewOnly)
    <button class="btn btn-icon btn-warning btnEditLicense" data-id="{{ $history?->id }}" title="แก้ไข"
      {{ $history ? '' : 'disabled' }}>
      <i class="bx bx-edit"></i>
    </button>
  @endunless

  @if ($history?->finance_approved)
    {{-- <span class="badge bg-success">อนุมัติแล้ว</span> --}}
  @else
    @if ($canFinance)
      {{-- ข้อมูลที่ยังกรอกไม่ครบ ส่งไปกับปุ่ม เพื่อดักตั้งแต่ก่อนเปิด dialog ยืนยัน
           (ฝั่ง server ดักซ้ำด้วยกติกาชุดเดียวกันใน approveFinance) --}}
      @php $approveMissing = $history->approveFinanceMissing(); @endphp
      <button class="btn btn-icon btn-success btnApproveFinance" data-id="{{ $history?->id }}"
        data-missing="{{ implode(', ', $approveMissing) }}"
        title="{{ $approveMissing ? 'ยังกรอกข้อมูลไม่ครบ : ' . implode(', ', $approveMissing) : 'ยืนยันการจ่ายเงินจริง' }}"
        {{ $history ? '' : 'disabled' }}>
        <i class="bx bx-check"></i>
      </button>
    @endif

    {{-- คืนป้ายก่อนปิดเงิน — เคสลูกค้ายังไม่มารับเงินคืน แต่ป้ายต้องเอาไปผูกกับลูกค้ารายใหม่แล้ว
         กดแล้วป้ายกลับเข้าสต็อกทันที ส่วนเรื่องเงินไปตามเก็บที่เมนู "ค้างคืนเงินป้ายแดง"
         สิทธิ์กว้างกว่าปุ่มปิดเงิน (RETURN_PLATE_EARLY_ROLES) — md กดได้ด้วย --}}
    @if ($canReturnEarly && !$history?->plate_returned_at)
      <button class="btn btn-icon btn-pink btnReturnPlateEarly" data-id="{{ $history?->id }}"
        data-plate="{{ $plate->number ?? '' }}"
        title="คืนป้ายก่อน (ยังไม่ปิดเงิน)">
        <i class="bx bx-undo"></i>
      </button>
    @endif
  @endif
@else
  <span class="text-muted">ว่าง</span>
@endif
{{-- <button class="btn btn-icon btn-danger btnDeleteLicense" data-id="{{ $p->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button> --}}

{{-- สีไอคอนย้ายไปประกาศที่ view.blade.php แล้ว — ห้ามใส่ <style> ในไฟล์นี้
     เพราะ partial นี้ถูก render เป็น HTML ของ "แถว" DataTables พอค้นหา/เปลี่ยนหน้า
     แถวที่ไม่ตรงจะถูกถอดออกจาก DOM ทำให้ style หายไปทั้งตาราง --}}
