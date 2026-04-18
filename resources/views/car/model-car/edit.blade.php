<div class="modal fade editCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลรถรุ่นหลัก</h6>
            <small class="text-white mf-hd-sub">Edit Car Model</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('model-car.update', $car->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section : ชื่อรุ่น --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ชื่อรุ่น</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="edit_Name_TH" class="mf-label form-label">
                    <i class="bx bx-font ci-indigo"></i> ชื่อภาษาไทย <span class="text-danger">*</span>
                  </label>
                  <input id="edit_Name_TH" type="text" class="form-control @error('Name_TH') is-invalid @enderror"
                    name="Name_TH" value="{{ $car->Name_TH }}" required>
                  @error('Name_TH')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-12">
                  <label for="edit_Name_EN" class="mf-label form-label">
                    <i class="bx bx-font-family ci-indigo"></i> ชื่อภาษาอังกฤษ <span class="text-danger">*</span>
                  </label>
                  <input id="edit_Name_EN" type="text" class="form-control @error('Name_EN') is-invalid @enderror"
                    name="Name_EN" value="{{ $car->Name_EN }}" required>
                  @error('Name_EN')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-12">
                  <label for="edit_initials" class="mf-label form-label">
                    <i class="bx bx-text ci-indigo"></i> ชื่อย่อ <span class="text-danger">*</span>
                  </label>
                  <input id="edit_initials" type="text" class="form-control @error('initials') is-invalid @enderror"
                    name="initials" value="{{ $car->initials }}" required>
                  @error('initials')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Section : ข้อมูลการเงิน --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการเงิน</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-6">
                  <label for="edit_over_budget" class="mf-label form-label">
                    <i class="bx bx-trending-up ci-amber"></i> ยอดเงินเกินงบ
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_over_budget" type="text" class="form-control text-end money-input"
                      name="over_budget"
                      value="{{ $car->over_budget !== null ? number_format($car->over_budget, 2) : '' }}"
                      placeholder="0.00">
                  </div>
                </div>

                <div class="col-6">
                  <label for="edit_money_min" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> เงินจองขั้นต่ำ
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_money_min" type="text" class="form-control text-end money-input" name="money_min"
                      value="{{ $car->money_min !== null ? number_format($car->money_min, 2) : '' }}"
                      placeholder="0.00">
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateCar">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
