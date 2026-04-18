<div class="modal fade inputColorSub" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-palette fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลสี</h6>
            <small class="text-white mf-hd-sub">Add Car Color</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('model.color.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Section : เลือกรุ่นรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">เลือกรุ่นรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="color_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก
                  </label>
                  <select id="color_model_id" class="form-select">
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <label for="subcarmodel_id" class="mf-label form-label">
                    <i class="bx bx-subdirectory-right"></i> รุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <select id="subcarmodel_id" name="subcarmodel_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Section : เลือกสี --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div
                style="width:30px;height:30px;border-radius:8px;background:#fce7f3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bx bxs-color-fill" style="color:#db2777;font-size:.9rem;"></i>
              </div>
              <span class="mf-section-title">เลือกสี</span>
              <span style="margin-left:auto;font-size:.76rem;color:#94a3b8;">สามารถเลือกได้หลายสี</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="color_id" class="mf-label form-label">
                    <i class="bx bx-palette" style="color:#db2777;"></i> สี <span class="text-danger">*</span>
                  </label>
                  <select name="color_id[]" id="color_id" multiple class="form-select" required
                    style="min-height:140px;">
                    @foreach ($gwmColor as $gm)
                      <option value="{{ $gm->id }}">{{ $gm->name }}</option>
                    @endforeach
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="submit" class="btn btn-primary px-5 btnStoreColorSub">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
