<div class="modal fade inputPricelistCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2">เพิ่มข้อมูลราคารถ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('model.pricelist-car.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-6 mb-5">
              <label for="pl_model_id" class="form-label">รุ่นรถหลัก <span class="text-danger">*</span></label>
              <select id="pl_model_id" name="model_id" class="form-select" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($models as $m)
                <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6 mb-5">
              <label for="pl_subModel_id" class="form-label">รุ่นรถย่อย <span class="text-danger">*</span></label>
              <select id="pl_subModel_id" name="subModel_id" class="form-select" required disabled>
                <option value="">-- เลือกรุ่นรถย่อย --</option>
              </select>
            </div>

            <div class="col-md-4 mb-5">
              <label for="pl_year" class="form-label">ปี <span class="text-danger">*</span></label>
              <input id="pl_year" type="text" class="form-control" name="year"
                placeholder="เช่น 2025" autocomplete="off" required>
            </div>

            @if ($brand == 1)
            <div class="col-md-4 mb-5">
              <label for="pl_option" class="form-label">Option</label>
              <input id="pl_option" type="text" class="form-control" name="option"
                autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="pl_color" class="form-label">ประเภทสี</label>
              <input id="pl_color" type="text" class="form-control" name="color"
                autocomplete="off">
            </div>
            @endif

            <div class="col-md-4 mb-5">
              <label for="pl_dnp" class="form-label">ราคาทุน (DNP)</label>
              <input id="pl_dnp" type="text" class="form-control text-end money-input"
                name="dnp" autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="pl_msrp" class="form-label">ราคาขาย (MSRP)</label>
              <input id="pl_msrp" type="text" class="form-control text-end money-input"
                name="msrp" autocomplete="off">
            </div>

            @if ($brand == 1)
            <div class="col-md-4 mb-5">
              <label for="pl_dm" class="form-label">DM</label>
              <input id="pl_dm" type="text" class="form-control text-end money-input"
                name="dm" autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="pl_ri" class="form-label">RI</label>
              <input id="pl_ri" type="text" class="form-control text-end money-input"
                name="ri" autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="pl_ws" class="form-label">WS</label>
              <input id="pl_ws" type="text" class="form-control text-end money-input"
                name="ws" autocomplete="off">
            </div>
            @endif
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStorePricelistCar">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
