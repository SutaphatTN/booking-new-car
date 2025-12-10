<div class="modal fade editSubCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editSubCarLabel">แก้ไขข้อมูลรถรุ่นย่อย</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('model.sub-model.update', ['sub_model_car' => $sub->id]) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-12 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ $m->id }}" {{ $sub->model_id == $m->id ? 'selected' : '' }}>{{ $m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="name" class="form-label">ชื่อรุ่นรถย่อย</label>
              <input id="name" type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name" value="{{ $sub->name }}" required>

              @error('name')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <!-- <div class="col-md-8 mb-5">
              <label for="code" class="form-label">รหัสรถ</label>
              <input id="code" type="text"
                class="form-control @error('code') is-invalid @enderror"
                name="code" value="{{ $sub->code }}" required>

              @error('code')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div> -->

            <div class="col-md-12 mb-5">
              <label for="detail" class="form-label">รายละเอียด</label>
              <textarea id="detail"
                class="form-control @error('detail') is-invalid @enderror"
                name="detail"
                rows="3" required>{{ $sub->detail }}</textarea>

              @error('detail')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="over_budget" class="form-label">ยอดเงินเกินงบ</label>
              <input id="over_budget" type="text"
                class="form-control text-end money-input"
                name="over_budget" value="{{ $sub->over_budget }}">
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateSubCar">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>