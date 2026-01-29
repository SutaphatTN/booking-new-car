<div class="modal fade inputFinExtraCom" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputFinExtraComLabel">เพิ่มข้อมูลไฟแนนซ์ Com Extra</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('finance.store-extra-com') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-12 mb-5">
              <label for="financeID" class="form-label">ชื่อไฟแนนซ์</label>
              <select id="financeID" name="financeID" class="form-select @error('financeID') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นไฟแนนซ์ --</option>
                @foreach ($financeAll as $fn)
                <option value="{{ $fn->id }}">{{ $fn->FinanceCompany }}</option>
                @endforeach
              </select>

              @error('financeID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

             <div class="col-md-12 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="com" class="form-label">Com Extra</label>
              <input id="com" type="text"
                class="form-control @error('com') is-invalid @enderror text-end money-input"
                name="com" required>

              @error('com')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreFinanceExtraCom">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>