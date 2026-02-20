<div class="modal fade editCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editCarLabel">แก้ไขข้อมูลรถรุ่นหลัก</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('model-car.update', $car->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-12 mb-5">
              <label for="Name_TH" class="form-label">ชื่อภาษาไทย</label>
              <input id="Name_TH" type="text"
                class="form-control @error('Name_TH') is-invalid @enderror"
                name="Name_TH" value="{{ $car->Name_TH }}" required>

              @error('Name_TH')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="Name_EN" class="form-label">ชื่อภาษาอังกฤษ</label>
              <input id="Name_EN" type="text"
                class="form-control @error('Name_EN') is-invalid @enderror"
                name="Name_EN" value="{{ $car->Name_EN }}" required>

              @error('Name_EN')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="initials" class="form-label">ชื่อย่อ</label>
              <input id="initials" type="text"
                class="form-control @error('initials') is-invalid @enderror"
                name="initials" value="{{ $car->initials }}" required>

              @error('initials')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="over_budget" class="form-label">ยอดเงินเกินงบ</label>
              <input id="over_budget" type="text"
                class="form-control text-end money-input"
                name="over_budget" value="{{ $car->over_budget !== null ? number_format($car->over_budget, 2) : '' }}">
            </div>

            <div class="col-md-12 mb-5">
              <label for="money_min" class="form-label">เงินจองขั้นต่ำ</label>
              <input id="money_min" type="text"
                class="form-control text-end money-input"
                name="money_min" value="{{ $car->money_min !== null ? number_format($car->money_min, 2) : '' }}">
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateCar">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>