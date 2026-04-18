<div class="modal fade inputCamAppellation" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-spreadsheet fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มชื่อแคมเปญ</h6>
            <small class="text-white mf-hd-sub">Add Campaign Name</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('campaign.storeAppellation') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="inp_cam_name" class="mf-label form-label">
                    <i class="bx bx-font"></i> ชื่อแคมเปญ <span class="text-danger">*</span>
                  </label>
                  <input id="inp_cam_name" type="text" class="form-control @error('name') is-invalid @enderror"
                    name="name" autocomplete="off" placeholder="ระบุชื่อแคมเปญ..." required>
                  @error('name')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
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
            <button type="button" class="btn btn-primary px-5 btnStoreCampaignAppellation">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
