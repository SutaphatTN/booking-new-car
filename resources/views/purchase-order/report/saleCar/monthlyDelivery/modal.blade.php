<div class="modal fade viewExportMonthlyDelivery" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-download fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายงานส่งมอบประจำเดือน</h6>
            <small class="text-white mf-hd-sub">เลือกเดือนที่ต้องการ</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mf-body">
        <form action="{{ route('purchase-order.monthlyDelivery-export') }}" method="GET">
          <div class="row g-3">
            <div class="col-12">
              <label class="mf-label form-label"><i class="bx bx-transfer-alt"></i> ประเภท</label>
              <div class="d-flex flex-column gap-1 mt-1">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="date_type" id="type_dms" value="dms" checked>
                  <label class="form-check-label" for="type_dms">วันส่งมอบบริษัท</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="date_type" id="type_ck" value="ck">
                  <label class="form-check-label" for="type_ck">วันส่งมอบของเซลล์</label>
                </div>
              </div>
            </div>
            <div class="col-12">
              <label for="from_date" class="mf-label form-label"><i class="bx bx-calendar"></i> เลือกเดือน</label>
              <input type="month" id="from_date" name="from_date" class="form-control">
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ยกเลิก</button>
            <button type="submit" class="btn btn-success px-4"><i class="bx bx-download me-1"></i>Export</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
