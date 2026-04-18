<div class="modal fade inputCam" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-spreadsheet fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลแคมเปญ</h6>
            <small class="text-white mf-hd-sub">Add Campaign</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('campaign.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Section 1 : ข้อมูลรุ่นรถและแคมเปญ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรุ่นรถและแคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-5">
                  <label for="inp_cam_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="inp_cam_model_id" name="model_id"
                    class="form-select @error('model_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-7">
                  <label for="inp_cam_subModel_id" class="mf-label form-label">
                    <i class="bx bx-subdirectory-right"></i> รุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <select id="inp_cam_subModel_id" name="subModel_id"
                    class="form-select @error('subModel_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                  @error('subModel_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-7">
                  <label for="inp_cam_camName_id" class="mf-label form-label">
                    <i class="bx bx-spreadsheet"></i> ชื่อแคมเปญ <span class="text-danger">*</span>
                  </label>
                  <select id="inp_cam_camName_id" name="camName_id" class="form-select" required>
                    <option value="">— เลือกแคมเปญ —</option>
                    @foreach ($camApp as $item)
                      <option value="{{ @$item->id }}">{{ @$item->name }}</option>
                    @endforeach
                  </select>
                  @error('camName_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-5">
                  <label for="inp_cam_campaign_type" class="mf-label form-label">
                    <i class="bx bx-list-ul"></i> ประเภท <span class="text-danger">*</span>
                  </label>
                  <select id="inp_cam_campaign_type" name="campaign_type"
                    class="form-select @error('campaign_type') is-invalid @enderror" required>
                    <option value="">— เลือกประเภท —</option>
                    @foreach ($type as $t)
                      <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                    @endforeach
                  </select>
                  @error('campaign_type')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลการเงิน --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการเงิน</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="inp_cam_cashSupport" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> เงินการขาย <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_cam_cashSupport" type="text"
                      class="form-control text-end money-input @error('cashSupport') is-invalid @enderror"
                      name="cashSupport" placeholder="0.00" required>
                  </div>
                  @error('cashSupport')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="inp_cam_cashSupport_deduct" class="mf-label form-label">
                    <i class="bx bx-minus-circle ci-amber"></i> เงินหัก <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_cam_cashSupport_deduct" type="text"
                      class="form-control text-end money-input @error('cashSupport_deduct') is-invalid @enderror"
                      name="cashSupport_deduct" placeholder="0.00" required>
                  </div>
                  @error('cashSupport_deduct')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="inp_cam_cashSupport_final" class="mf-label form-label">
                    <i class="bx bx-check-circle ci-amber"></i> จำนวนเงินที่เหลือ
                    <span class="mf-label-note">(คำนวณอัตโนมัติ)</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-slate">฿</span>
                    <input id="inp_cam_cashSupport_final" type="text"
                      class="form-control text-end money-input form-control-plaintext-mf"
                      name="cashSupport_final" placeholder="0.00" readonly>
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
              <span class="mf-section-title">ช่วงเวลาแคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="inp_cam_startYear" class="mf-label form-label">
                    <i class="bx bx-calendar-plus ci-emerald"></i> ตั้งแต่ปี <span class="text-danger">*</span>
                  </label>
                  <input id="inp_cam_startYear" type="number"
                    class="form-control @error('startYear') is-invalid @enderror" name="startYear"
                    placeholder="เช่น 2025" required>
                  @error('startYear')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="inp_cam_endYear" class="mf-label form-label">
                    <i class="bx bx-calendar-minus ci-emerald"></i> ถึงปี <span class="text-danger">*</span>
                  </label>
                  <input id="inp_cam_endYear" type="number"
                    class="form-control @error('endYear') is-invalid @enderror" name="endYear"
                    placeholder="เช่น 2025" required>
                  @error('endYear')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="inp_cam_startDate" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-emerald"></i> วันที่เริ่ม <span class="text-danger">*</span>
                  </label>
                  <input id="inp_cam_startDate" type="date"
                    class="form-control @error('startDate') is-invalid @enderror" name="startDate" required>
                  @error('startDate')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="inp_cam_endDate" class="mf-label form-label">
                    <i class="bx bx-calendar-x ci-emerald"></i> วันที่สิ้นสุด <span class="text-danger">*</span>
                  </label>
                  <input id="inp_cam_endDate" type="date"
                    class="form-control @error('endDate') is-invalid @enderror" name="endDate" required>
                  @error('endDate')
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
            <button type="button" class="btn btn-primary px-5 btnStoreCampaign">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
