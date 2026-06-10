<div class="modal fade editFilm" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขสต็อกฟิล์ม</h6>
            <small class="text-white mf-hd-sub">{{ $stock->stock_no }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('stock-film.update', $stock->id) }}" method="POST" id="formEditFilm">
          @csrf
          @method('PUT')

          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-film"></i>
              </div>
              <span class="mf-section-title">ข้อมูลฟิล์ม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-2">

                <div class="col-md-6">
                  <label for="ed_stock_no" class="mf-label form-label">Stock No.</label>
                  <input id="ed_stock_no" type="text"
                    class="form-control form-control-plaintext-mf fw-bold text-primary" value="{{ $stock->stock_no }}"
                    disabled>
                </div>

                <div class="col-md-6">
                  <label for="ed_part_no" class="mf-label form-label">Part No.</label>
                  <input id="ed_part_no" type="text" name="part_no" class="form-control"
                    value="{{ $stock->part_no }}" placeholder="ระบุ Part No. (ถ้ามี)" readonly>
                </div>

                <div class="col-md-7">
                  <label for="ed_film_brand" class="mf-label form-label">ยี่ห้อฟิล์ม</label>
                  <input id="ed_film_brand" type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $stock->filmBrand?->name ?? '-' }}" disabled>
                </div>

                <div class="col-md-5">
                  <label for="ed_shade" class="mf-label form-label">ความเข้ม</label>
                  <input id="ed_shade" type="text" class="form-control form-control-plaintext-mf text-center"
                    value="{{ $stock->shade }}" disabled>
                </div>

                <div class="col-md-6">
                  <label for="ed_withdrawal_date" class="mf-label form-label">วันที่เบิก</label>
                  <input id="ed_withdrawal_date" type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $stock->withdrawal_date?->format('d/m/Y') }}" disabled>
                </div>

                <div class="col-md-6">
                  <label for="ed_initial_qty" class="mf-label form-label">
                    จำนวนเริ่มต้น (ตร.ฟุต) <span class="text-danger">*</span>
                  </label>
                  <input id="ed_initial_qty" type="number" name="initial_qty" class="form-control text-end"
                    value="{{ $stock->initial_qty }}" min="0" step="0.01" readonly>
                </div>

                <div class="col-md-6">
                  <label for="ed_used_qty" class="mf-label form-label">ใช้ไปแล้ว (ตร.ฟุต)</label>
                  <input id="ed_used_qty" type="text"
                    class="form-control form-control-plaintext-mf text-end text-danger fw-bold"
                    value="{{ number_format($stock->used_qty, 2) }}" disabled>
                </div>

                <div class="col-md-6">
                  <label for="ed_remaining_qty" class="mf-label form-label">คงเหลือ (ตร.ฟุต)</label>
                  <input id="ed_remaining_qty" type="text"
                    class="form-control form-control-plaintext-mf text-end text-success fw-bold"
                    value="{{ number_format($stock->remaining_qty, 2) }}" disabled>
                </div>

              </div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-search-alt"></i>
              </div>
              <span class="mf-section-title">ผู้ตรวจสอบ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-6">
                  <label for="ed_inspection_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check"></i> วันที่ตรวจสอบ
                  </label>
                  <input id="ed_inspection_date" type="date" name="inspection_date" class="form-control"
                    value="{{ $stock->inspection_date?->format('Y-m-d') }}">
                </div>

                <div class="col-md-6">
                  <label for="ed_inspection_qty" class="mf-label form-label">
                    <i class="bx bx-ruler"></i> ตรวจสอบคงเหลือ (ตร.ฟุต)
                  </label>
                  <input id="ed_inspection_qty" type="number" name="inspection_qty" class="form-control text-end"
                    value="{{ $stock->inspection_qty }}" min="0" step="0.01">
                </div>

                <div class="col-md-12">
                  <label for="ed_inspection_result" class="mf-label form-label">
                    <i class="bx bx-check-shield"></i> ผลการตรวจนับ
                  </label>
                  <select id="ed_inspection_result" name="inspection_result" class="form-select">
                    <option value="">— ยังไม่ตรวจ —</option>
                    <option value="pass" {{ $stock->inspection_result === 'pass' ? 'selected' : '' }}>ถูกต้อง
                    </option>
                    <option value="fail" {{ $stock->inspection_result === 'fail' ? 'selected' : '' }}>ไม่ถูกต้อง
                    </option>
                  </select>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i
                class="bx bx-x me-1"></i>ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateFilm">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
