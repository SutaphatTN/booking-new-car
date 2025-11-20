<div class="modal fade inputCam" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputCamLabel">เพิ่มข้อมูลแคมเปญ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('campaign.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-6 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ @$m->id }}">{{ @$m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
              <select id="subModel_id" name="subModel_id" class="form-select @error('subModel_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถย่อย --</option>
              </select>

              @error('subModel_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-7 mb-5">
              <label for="name" class="form-label">ชื่อแคมเปญ</label>
              <input id="name" type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name" required>

              @error('name')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-5 mb-5">
              <label for="campaign_type" class="form-label">ประเภท</label>
              <select id="campaign_type" name="campaign_type" class="form-select @error('campaign_type') is-invalid @enderror" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                @foreach ($type as $t)
                <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                @endforeach
              </select>

              @error('campaign_type')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="cashSupport" class="form-label">เงินการขาย</label>
              <input id="cashSupport" type="text"
                class="form-control text-end money-input @error('cashSupport') is-invalid @enderror"
                name="cashSupport" required>

              @error('cashSupport')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            
            <div class="col-md-4 mb-5">
              <label for="cashSupport_deduct" class="form-label">เงินหัก</label>
              <input id="cashSupport_deduct" type="text"
                class="form-control text-end money-input @error('cashSupport_deduct') is-invalid @enderror"
                name="cashSupport_deduct" required>

              @error('cashSupport_deduct')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="cashSupport_final" class="form-label">จำนวนเงินที่เหลือ</label>
              <input id="cashSupport_final" type="text"
                class="form-control text-end money-input @error('cashSupport_final') is-invalid @enderror"
                name="cashSupport_final" readonly>

              @error('cashSupport_final')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="startDate" class="form-label">วันที่เริ่ม</label>
              <input id="startDate" type="date"
                class="form-control @error('startDate') is-invalid @enderror"
                name="startDate" required>

              @error('startDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-6 mb-5">
              <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
              <input id="endDate" type="date"
                class="form-control @error('endDate') is-invalid @enderror"
                name="endDate" required>

              @error('endDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreCampaign">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>