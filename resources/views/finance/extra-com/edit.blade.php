<div class="modal fade editFinExtraCom" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลไฟแนนซ์ Com Extra</h6>
            <small class="text-white mf-hd-sub">Edit Finance Com Extra</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form
          action="{{ route('finance.update-extra-com', $finExtra->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section : เลือกไฟแนนซ์และรุ่นรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-buildings"></i>
              </div>
              <span class="mf-section-title">เลือกไฟแนนซ์และรุ่นรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="edit_ec_financeID" class="mf-label form-label">
                    <i class="bx bx-building-house ci-indigo"></i> ชื่อไฟแนนซ์ <span class="text-danger">*</span>
                  </label>
                  <select id="edit_ec_financeID" name="financeID"
                    class="form-select @error('financeID') is-invalid @enderror" required>
                    <option value="">— เลือกไฟแนนซ์ —</option>
                    @foreach ($financeAll as $fn)
                      <option value="{{ $fn->id }}" {{ $finExtra->financeID == $fn->id ? 'selected' : '' }}>
                        {{ $fn->FinanceCompany }}
                      </option>
                    @endforeach
                  </select>
                  @error('financeID')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-12">
                  <label for="edit_ec_model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="edit_ec_model_id" name="model_id"
                    class="form-select @error('model_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}" {{ $finExtra->model_id == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}
                      </option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Section : ค่า Com Extra --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ค่า Com Extra</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-6">
                  <label for="edit_ec_com" class="mf-label form-label">
                    <i class="bx bx-badge-check"></i> Com Extra <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_ec_com" type="text"
                      class="form-control text-end money-input @error('com') is-invalid @enderror"
                      name="com" value="{{ $finExtra->com !== null ? number_format($finExtra->com, 2) : '' }}"
                      placeholder="0.00" required>
                  </div>
                  @error('com')
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
            <button type="button" class="btn btn-primary px-5 btnUpdateFinanceExtraCom">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
