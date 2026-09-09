<div class="modal fade viewExportStaffCom" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-download fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายงานค่าคอมฝ่ายสนับสนุน</h6>
            <small class="text-white mf-hd-sub">เลือกเดือนที่ต้องการ</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mf-body">
        {{-- เลือกเป็น "เดือน" เท่านั้น — ค่าคอมฝ่ายสนับสนุนคิดจากจำนวนคันที่ตัด CK ทั้งเดือน
             ถ้าให้เลือกช่วงวันที่ ยอดจะไม่ตรงกับขั้นบันไดใน config/staff_commission.php --}}
        <form action="{{ route('commission.staff.export') }}" method="GET">
          <div class="row g-3">
            <div class="col-12">
              <label for="staffReportMonth" class="mf-label form-label">
                <i class="bx bx-calendar"></i> เดือนที่ต้องการ
              </label>
              <input type="month" id="staffReportMonth" name="month" class="form-control" required
                value="{{ now()->format('Y-m') }}">
              <div class="mt-1 text-muted" style="font-size:.78rem;">
                <i class="bx bx-info-circle me-1"></i>
                ได้ 2 ชีท — สรุปรายคน และรายละเอียดตามเกณฑ์ ยอดตรงกับตารางในหน้านี้
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="submit" class="btn btn-success px-4">
              <i class="bx bx-download me-1"></i>Export
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
