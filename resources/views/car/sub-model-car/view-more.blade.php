<div class="modal fade viewSubCar" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลรุ่นรถย่อย</h6>
            <small class="text-white mf-hd-sub">Sub Model Detail</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section : ข้อมูลรุ่นรถ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-purchase-tag"></i>
            </div>
            <span class="mf-section-title">ข้อมูลรุ่นรถ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-8">
                <label class="mf-label form-label">
                  <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก
                </label>
                <input class="form-control" type="text" value="{{ $sub->model->Name_TH }}" disabled>
              </div>

              <div class="col-md-4">
                <label class="mf-label form-label">
                  <i class="bx bx-list-ul ci-indigo"></i> ประเภท
                </label>
                <input class="form-control" type="text" value="{{ $sub->typeCar->name }}" disabled>
              </div>

              <div class="col-12">
                <label class="mf-label form-label">
                  <i class="bx bx-font ci-indigo"></i> ชื่อรุ่นรถย่อย
                </label>
                <input class="form-control" type="text" value="{{ $sub->name }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section : รายละเอียด --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon emerald">
              <i class="bx bx-notepad"></i>
            </div>
            <span class="mf-section-title">รายละเอียด</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-12">
                <label class="mf-label form-label">
                  <i class="bx bx-align-left ci-emerald"></i> รายละเอียด
                </label>
                <textarea class="form-control" rows="3" disabled>{{ $sub->detail ?: '-' }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- Actions --}}
        {{-- <div class="d-flex justify-content-end pt-1">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ปิด
          </button>
        </div> --}}

      </div>

    </div>
  </div>
</div>
