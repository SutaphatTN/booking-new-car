<div class="modal fade editFin" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editFinLabel">แก้ไขข้อมูลไฟแนนซ์</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('finance.update', $fin->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-12 mb-5">
              <label for="FinanceCompany" class="form-label">ชื่อไฟแนนซ์</label>
              <input id="FinanceCompany" type="text"
                class="form-control @error('FinanceCompany') is-invalid @enderror"
                name="FinanceCompany" value="{{ $fin->FinanceCompany }}" required>

              @error('FinanceCompany')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="tax" class="form-label">ภาษีหัก ณ ที่จ่าย</label>
              <input id="tax" type="text"
                class="form-control @error('tax') is-invalid @enderror"
                name="tax" value="{{ $fin->tax }}" required>

              @error('tax')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="max_year" class="form-label">จำนวนปีสูงสุด</label>
              <input id="max_year" type="text"
                class="form-control @error('max_year') is-invalid @enderror"
                name="max_year" value="{{ $fin->max_year }}" required>

              @error('max_year')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateFinance">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>