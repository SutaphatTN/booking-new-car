<div class="modal fade editPricelistCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลราคารถ</h6>
            <small class="text-white mf-hd-sub">Edit Pricelist</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('model.pricelist-car.update', $price->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

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
                  <label for="edit_pl_model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="edit_pl_model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}" {{ $price->model_id == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="edit_pl_subModel_id" class="mf-label form-label">
                    <i class="bx bx-subdirectory-right ci-indigo"></i> รุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <select id="edit_pl_subModel_id" name="subModel_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                    @foreach ($subModels as $s)
                      <option value="{{ $s->id }}" {{ $price->subModel_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="edit_pl_year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> ปี <span class="text-danger">*</span>
                  </label>
                  <input id="edit_pl_year" type="text" class="form-control" name="year"
                    value="{{ $price->year }}" autocomplete="off" required>
                </div>

                @if ($brand == 1)
                  <div class="col-md-4">
                    <label for="edit_pl_option" class="mf-label form-label">
                      <i class="bx bx-slider-alt ci-indigo"></i> Option
                    </label>
                    <input id="edit_pl_option" type="text" class="form-control" name="option"
                      value="{{ $price->option }}" autocomplete="off">
                  </div>

                  <div class="col-md-4">
                    <label for="edit_pl_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-indigo"></i> ประเภทสี
                    </label>
                    <input id="edit_pl_color" type="text" class="form-control" name="color"
                      value="{{ $price->color }}" autocomplete="off">
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
                  <label for="edit_pl_dnp" class="mf-label form-label">
                    <i class="bx bx-store ci-amber"></i> ราคาทุน (DNP)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_pl_dnp" type="text" class="form-control text-end money-input" name="dnp"
                      value="{{ $price->dnp !== null ? number_format($price->dnp, 2) : '' }}" autocomplete="off"
                      placeholder="0.00">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="edit_pl_msrp" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย (MSRP)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_pl_msrp" type="text" class="form-control text-end money-input" name="msrp"
                      value="{{ $price->msrp !== null ? number_format($price->msrp, 2) : '' }}" autocomplete="off"
                      placeholder="0.00">
                  </div>
                </div>

                @if ($brand == 1)
                  <div class="col-md-4">
                    <label for="edit_pl_dm" class="mf-label form-label">
                      <i class="bx bx-trending-down ci-amber"></i> DM
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="edit_pl_dm" type="text" class="form-control text-end money-input" name="dm"
                        value="{{ $price->dm !== null ? number_format($price->dm, 2) : '' }}" autocomplete="off"
                        placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="edit_pl_ri" class="mf-label form-label">
                      <i class="bx bx-badge-check ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="edit_pl_ri" type="text" class="form-control text-end money-input" name="ri"
                        value="{{ $price->ri !== null ? number_format($price->ri, 2) : '' }}" autocomplete="off"
                        placeholder="0.00">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="edit_pl_ws" class="mf-label form-label">
                      <i class="bx bx-transfer ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="edit_pl_ws" type="text" class="form-control text-end money-input" name="ws"
                        value="{{ $price->ws !== null ? number_format($price->ws, 2) : '' }}" autocomplete="off"
                        placeholder="0.00">
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
            <button type="button" class="btn btn-primary px-5 btnUpdatePricelistCar">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
