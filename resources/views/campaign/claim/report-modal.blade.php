<div class="modal fade" id="claimReportModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-spreadsheet fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายงานรายการใช้แคมเปญ</h6>
            <small class="text-white mf-hd-sub">เลือกช่วงวันที่ส่งมอบ</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mf-body">
        <form action="{{ route('campaign.claim.report') }}" method="GET">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="report_from_date" class="mf-label form-label">
                <i class="bx bx-calendar"></i> ตั้งแต่วันที่
              </label>
              <input type="date" id="report_from_date" name="from_date" class="form-control"
                value="{{ now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="col-md-6">
              <label for="report_to_date" class="mf-label form-label">
                <i class="bx bx-calendar"></i> ถึงวันที่
              </label>
              <input type="date" id="report_to_date" name="to_date" class="form-control"
                value="{{ now()->format('Y-m-d') }}">
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
