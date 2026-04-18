<div class="modal fade viewCam" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลแคมเปญ</h6>
            <small class="text-white mf-hd-sub">Campaign Detail</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section 1 : ข้อมูลรุ่นรถและแคมเปญ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-purchase-tag"></i>
            </div>
            <span class="mf-section-title">ข้อมูลรุ่นรถและแคมเปญ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-5">
                <label for="model_id" class="mf-label form-label">
                  <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก
                </label>
                <input id="model_id" class="form-control" type="text" value="{{ $cam->model->Name_TH }}" disabled>
              </div>

              <div class="col-md-7">
                <label for="subModel_id" class="mf-label form-label">
                  <i class="bx bx-subdirectory-right ci-indigo"></i> รุ่นรถย่อย
                </label>
                <input id="subModel_id" class="form-control" type="text"
                  value="{{ !empty($cam->subModel) ? ($cam->subModel->detail ? $cam->subModel->detail . ' - ' . $cam->subModel->name : $cam->subModel->name) : '' }}"
                  disabled>
              </div>

              <div class="col-md-7">
                <label for="camName_id" class="mf-label form-label">
                  <i class="bx bx-spreadsheet ci-indigo"></i> ชื่อแคมเปญ
                </label>
                <input id="camName_id" class="form-control" type="text" value="{{ $cam->appellation->name }}" disabled>
              </div>

              <div class="col-md-5">
                <label for="campaign_type" class="mf-label form-label">
                  <i class="bx bx-list-ul ci-indigo"></i> ประเภท
                </label>
                <input id="campaign_type" class="form-control" type="text" value="{{ $cam->type->name }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 2 : ข้อมูลการเงิน --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon amber">
              <i class="bx bx-money"></i>
            </div>
            <span class="mf-section-title">ข้อมูลการเงิน</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-4">
                <label for="cashSupport" class="mf-label form-label">
                  <i class="bx bx-wallet ci-amber"></i> เงินการขาย
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="cashSupport" class="form-control text-end" type="text"
                    value="{{ $cam->cashSupport !== null ? number_format($cam->cashSupport, 2) : '-' }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="cashSupport_deduct" class="mf-label form-label">
                  <i class="bx bx-minus-circle ci-amber"></i> เงินหัก
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="cashSupport_deduct" class="form-control text-end" type="text"
                    value="{{ $cam->cashSupport_deduct !== null ? number_format($cam->cashSupport_deduct, 2) : '-' }}"
                    disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="cashSupport_final" class="mf-label form-label">
                  <i class="bx bx-check-circle ci-amber"></i> จำนวนเงินที่เหลือ
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="cashSupport_final" class="form-control text-end" type="text"
                    value="{{ $cam->cashSupport_final !== null ? number_format($cam->cashSupport_final, 2) : '-' }}"
                    disabled>
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 3 : ช่วงเวลา --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon emerald">
              <i class="bx bx-calendar"></i>
            </div>
            <span class="mf-section-title">ช่วงเวลาแคมเปญ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-3">
                <label for="startYear" class="mf-label form-label">
                  <i class="bx bx-calendar-plus ci-emerald"></i> ตั้งแต่ปี
                </label>
                <input id="startYear" class="form-control" type="text" value="{{ $cam->startYear }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="endYear" class="mf-label form-label">
                  <i class="bx bx-calendar-minus ci-emerald"></i> ถึงปี
                </label>
                <input id="endYear" class="form-control" type="text" value="{{ $cam->endYear }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="startDate" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-emerald"></i> วันที่เริ่ม
                </label>
                <input id="startDate" class="form-control" type="text" value="{{ $cam->format_start_date }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="endDate" class="mf-label form-label">
                  <i class="bx bx-calendar-x ci-emerald"></i> วันที่สิ้นสุด
                </label>
                <input id="endDate" class="form-control" type="text" value="{{ $cam->format_end_date }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex justify-content-end pt-1">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ปิด
          </button>
        </div>

      </div>

    </div>
  </div>
</div>
