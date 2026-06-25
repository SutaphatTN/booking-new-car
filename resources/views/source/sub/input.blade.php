<div class="modal fade inputSub" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-sitemap fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มแหล่งที่มาย่อย</h6>
            <small class="text-white mf-hd-sub">Add Sub-source</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('source.sub.store') }}" method="POST">
          @csrf

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแหล่งที่มาย่อย</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="inp_sub_name" class="mf-label form-label">
                    <i class="bx bx-font"></i> ชื่อแหล่งที่มาย่อย <span class="text-danger">*</span>
                  </label>
                  <input id="inp_sub_name" type="text" class="form-control" name="name"
                    autocomplete="off" placeholder="ระบุชื่อแหล่งที่มาย่อย..." required>
                </div>

                <div class="col-12">
                  <label for="inp_sub_main" class="mf-label form-label">
                    <i class="bx bx-layer"></i> แหล่งที่มาหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="inp_sub_main" name="main_source" class="form-select" required>
                    <option value="">— เลือก —</option>
                    {{-- แสดงเฉพาะ Offline / Online / Walk-in --}}
                    @foreach (\Illuminate\Support\Arr::only($mains, ['offline', 'online', 'walkin']) as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreSub">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
