<div class="modal fade viewAcc" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลประดับยนต์</h6>
            <small class="text-white mf-hd-sub">Accessory Detail</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section 1 : ข้อมูลทั่วไป --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-purchase-tag"></i>
            </div>
            <span class="mf-section-title">ข้อมูลทั่วไป</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-6">
                <label for="model_id" class="mf-label form-label">
                  <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก
                </label>
                <input id="model_id" class="form-control" type="text" value="{{ $acc->model->Name_TH }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="accessory_id" class="mf-label form-label">
                  <i class="bx bx-barcode ci-indigo"></i> รหัสเครื่องประดับ
                </label>
                <input id="accessory_id" class="form-control" type="text" value="{{ $acc->accessory_id }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="accessoryPartner_id" class="mf-label form-label">
                  <i class="bx bx-store ci-indigo"></i> แหล่งที่มา
                </label>
                <input id="accessoryPartner_id" class="form-control" type="text" value="{{ $acc->partner->name }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="accessoryType_id" class="mf-label form-label">
                  <i class="bx bx-list-ul ci-indigo"></i> ประเภท
                </label>
                <input id="accessoryType_id" class="form-control" type="text" value="{{ $acc->type->name }}" disabled>
              </div>

              <div class="col-12">
                <label for="detail" class="mf-label form-label">
                  <i class="bx bx-align-left ci-indigo"></i> รายละเอียด
                </label>
                <textarea id="detail" class="form-control" rows="3" disabled>{{ $acc->detail ?: '-' }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 2 : ข้อมูลราคา --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon amber">
              <i class="bx bx-money"></i>
            </div>
            <span class="mf-section-title">ข้อมูลราคา</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-4">
                <label for="cost_spare" class="mf-label form-label">
                  <i class="bx bx-wrench ci-amber"></i> ราคาทุนอะไหล่
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="cost_spare" class="form-control text-end" type="text"
                    value="{{ $acc->cost_spare !== null ? number_format($acc->cost_spare, 2) : '-' }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="cost" class="mf-label form-label">
                  <i class="bx bx-store ci-amber"></i> ราคาทุน
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="cost" class="form-control text-end" type="text"
                    value="{{ $acc->cost !== null ? number_format($acc->cost, 2) : '-' }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="promo" class="mf-label form-label">
                  <i class="bx bx-gift ci-amber"></i> ราคาพิเศษ
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="promo" class="form-control text-end" type="text"
                    value="{{ $acc->promo !== null ? number_format($acc->promo, 2) : '-' }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="sale" class="mf-label form-label">
                  <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="sale" class="form-control text-end" type="text"
                    value="{{ $acc->sale !== null ? number_format($acc->sale, 2) : '-' }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="comSale" class="mf-label form-label">
                  <i class="bx bx-badge-check ci-amber"></i> ค่าคอม ราคาขาย
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="comSale" class="form-control text-end" type="text"
                    value="{{ $acc->comSale !== null ? number_format($acc->comSale, 2) : '-' }}" disabled>
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
            <span class="mf-section-title">ช่วงเวลา</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-3">
                <label for="startDate" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-emerald"></i> วันที่เริ่ม
                </label>
                <input id="startDate" class="form-control" type="text" value="{{ $acc->format_start_date }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="endDate" class="mf-label form-label">
                  <i class="bx bx-calendar-x ci-emerald"></i> วันที่สิ้นสุด
                </label>
                <input id="endDate" class="form-control" type="text" value="{{ $acc->format_end_date }}" disabled>
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
