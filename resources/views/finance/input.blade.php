<div class="modal fade inputFin" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-building-house fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลไฟแนนซ์</h6>
            <small class="text-white mf-hd-sub">Add Finance Company</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form
          action="{{ route('finance.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          {{-- Section : ข้อมูลไฟแนนซ์ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-buildings"></i>
              </div>
              <span class="mf-section-title">ข้อมูลไฟแนนซ์</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="inp_FinanceCompany" class="mf-label form-label">
                    <i class="bx bx-building-house"></i> ชื่อไฟแนนซ์ <span class="text-danger">*</span>
                  </label>
                  <input id="inp_FinanceCompany" type="text"
                    class="form-control @error('FinanceCompany') is-invalid @enderror"
                    name="FinanceCompany" placeholder="ระบุชื่อบริษัทไฟแนนซ์..." required>
                  @error('FinanceCompany')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="inp_tax" class="mf-label form-label">
                    <i class="bx bx-percent"></i> ภาษีหัก ณ ที่จ่าย <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input id="inp_tax" type="text"
                      class="form-control text-end @error('tax') is-invalid @enderror"
                      name="tax" placeholder="0.00" required>
                    <span class="input-group-text ig-indigo">%</span>
                  </div>
                  @error('tax')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="inp_max_year" class="mf-label form-label">
                    <i class="bx bx-calendar"></i> จำนวนปีสูงสุด <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input id="inp_max_year" type="text"
                      class="form-control text-end @error('max_year') is-invalid @enderror"
                      name="max_year" placeholder="0" required>
                    <span class="input-group-text ig-indigo">ปี</span>
                  </div>
                  @error('max_year')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreFinance">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
