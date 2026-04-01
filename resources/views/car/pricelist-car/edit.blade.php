<div class="modal fade editPricelistCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2">แก้ไขข้อมูลราคารถ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('model.pricelist-car.update', $price->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-6 mb-5">
              <label for="edit_pl_model_id" class="form-label">รุ่นรถหลัก <span class="text-danger">*</span></label>
              <select id="edit_pl_model_id" name="model_id" class="form-select" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($models as $m)
                <option value="{{ $m->id }}" {{ $price->model_id == $m->id ? 'selected' : '' }}>
                  {{ $m->Name_TH }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6 mb-5">
              <label for="edit_pl_subModel_id" class="form-label">รุ่นรถย่อย <span class="text-danger">*</span></label>
              <select id="edit_pl_subModel_id" name="subModel_id" class="form-select" required>
                <option value="">-- เลือกรุ่นรถย่อย --</option>
                @foreach ($subModels as $s)
                <option value="{{ $s->id }}" {{ $price->subModel_id == $s->id ? 'selected' : '' }}>
                  {{ $s->name }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4 mb-5">
              <label for="edit_pl_year" class="form-label">ปี <span class="text-danger">*</span></label>
              <input id="edit_pl_year" type="text" class="form-control" name="year"
                value="{{ $price->year }}" autocomplete="off" required>
            </div>

            @if ($brand == 1)
            <div class="col-md-4 mb-5">
              <label for="edit_pl_option" class="form-label">Option</label>
              <input id="edit_pl_option" type="text" class="form-control" name="option"
                value="{{ $price->option }}" autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="edit_pl_color" class="form-label">ประเภทสี</label>
              <input id="edit_pl_color" type="text" class="form-control" name="color"
                value="{{ $price->color }}" autocomplete="off">
            </div>
            @endif

            <div class="col-md-4 mb-5">
              <label for="edit_pl_dnp" class="form-label">ราคาทุน (DNP)</label>
              <input id="edit_pl_dnp" type="text" class="form-control text-end money-input"
                name="dnp" value="{{ $price->dnp !== null ? number_format($price->dnp, 2) : '' }}"
                autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="edit_pl_msrp" class="form-label">ราคาขาย (MSRP)</label>
              <input id="edit_pl_msrp" type="text" class="form-control text-end money-input"
                name="msrp" value="{{ $price->msrp !== null ? number_format($price->msrp, 2) : '' }}"
                autocomplete="off">
            </div>

            @if ($brand == 1)
            <div class="col-md-4 mb-5">
              <label for="edit_pl_dm" class="form-label">DM</label>
              <input id="edit_pl_dm" type="text" class="form-control text-end money-input"
                name="dm" value="{{ $price->dm !== null ? number_format($price->dm, 2) : '' }}"
                autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="edit_pl_ri" class="form-label">RI</label>
              <input id="edit_pl_ri" type="text" class="form-control text-end money-input"
                name="ri" value="{{ $price->ri !== null ? number_format($price->ri, 2) : '' }}"
                autocomplete="off">
            </div>

            <div class="col-md-4 mb-5">
              <label for="edit_pl_ws" class="form-label">WS</label>
              <input id="edit_pl_ws" type="text" class="form-control text-end money-input"
                name="ws" value="{{ $price->ws !== null ? number_format($price->ws, 2) : '' }}"
                autocomplete="off">
            </div>
            @endif
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdatePricelistCar">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
