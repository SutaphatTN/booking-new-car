@php
  $user = auth()->user();
@endphp

@if ($history)
  <button class="btn btn-icon btn-info btnViewLicense" data-id="{{ $history?->id }}" title="ดูข้อมูล"
    {{ $history ? '' : 'disabled' }}>
    <i class="bx bx-show"></i>
  </button>
  <button class="btn btn-icon btn-warning btnEditLicense" data-id="{{ $history?->id }}" title="แก้ไข"
    {{ $history ? '' : 'disabled' }}>
    <i class="bx bx-edit"></i>
  </button>

  @if ($history?->finance_approved)
    {{-- <span class="badge bg-success">อนุมัติแล้ว</span> --}}
  @else
    @if (in_array(auth()->user()->role, ['account', 'admin', 'audit', 'audit_lead', 'audit_dp', 'gm']))
      <button class="btn btn-icon btn-success btnApproveFinance" data-id="{{ $history?->id }}"
        title="ยืนยันการจ่ายเงินจริง" {{ $history ? '' : 'disabled' }}>
        <i class="bx bx-check"></i>
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
