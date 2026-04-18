<div class="modal fade inputPricelistCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-tag fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลราคารถ</h6>
            <small class="text-white mf-hd-sub">Add New Pricelist</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('model.pricelist-car.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

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

                <div class="col-md-6">
                  <label for="pl_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="pl_model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="pl_subModel_id" class="mf-label form-label">
                    <i class="bx bx-subdirectory-right"></i> รุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <select id="pl_subModel_id" name="subModel_id" class="form-select" required disabled>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="pl_year" class="mf-label form-label">
                    <i class="bx bx-calendar"></i> ปี <span class="text-danger">*</span>
                  </label>
                  <input id="pl_year" type="text" class="form-control" name="year" placeholder="เช่น 2025"
                    autocomplete="off" required>
                </div>

                @if ($brand == 1)
                  <div class="col-md-4">
                    <label for="pl_option" class="mf-label form-label">
                      <i class="bx bx-slider-alt"></i> Option
                    </label>
                    <input id="pl_option" type="text" class="form-control" name="option" autocomplete="off">
                  </div>

                  <div class="col-md-4">
                    <label for="pl_color" class="mf-label form-label">
                      <i class="bx bx-palette"></i> ประเภทสี
                    </label>
                    <input id="pl_color" type="text" class="form-control" name="color" autocomplete="off">
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Section : ข้อมูลราคา --}}
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
                  <label for="pl_dnp" class="mf-label form-label">
                    <i class="bx bx-store ci-amber"></i> ราคาทุน (DNP)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="pl_dnp" type="text" class="form-control text-end money-input" name="dnp"
                      autocomplete="off" placeholder="0.00">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="pl_msrp" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย (MSRP)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="pl_msrp" type="text" class="form-control text-end money-input" name="msrp"
                      autocomplete="off" placeholder="0.00">
                  </div>
                </div>

                @if ($brand == 1)
                  <div class="col-md-4">
                    <label for="pl_dm" class="mf-label form-label">
                      <i class="bx bx-trending-down ci-amber"></i> DM
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="pl_dm" type="text" class="form-control text-end money-input" name="dm"
                        autocomplete="off" placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="pl_ri" class="mf-label form-label">
                      <i class="bx bx-badge-check ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="pl_ri" type="text" class="form-control text-end money-input" name="ri"
                        autocomplete="off" placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="pl_ws" class="mf-label form-label">
                      <i class="bx bx-transfer ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="pl_ws" type="text" class="form-control text-end money-input" name="ws"
                        autocomplete="off" placeholder="0.00">
                    </div>
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStorePricelistCar">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
