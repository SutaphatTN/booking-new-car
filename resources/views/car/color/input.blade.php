<div class="modal fade inputColorSub" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputColorSubLabel">เพิ่มข้อมูลสี</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('color.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-12 mb-5">
              <label class="form-label">รุ่นรถหลัก</label>

              <select id="model_id" class="form-select">
                <option value="">-- เลือกรุ่นรถหลัก --</option>

                @foreach ($model as $m)
                <option value="{{ $m->id }}">
                  {{ $m->Name_TH }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-12 mb-5">
              <label for="subcarmodel_id" class="form-label">รุ่นรถย่อย</label>
              <select id="subcarmodel_id" name="subcarmodel_id" class="form-select" required>
                <option value="">-- เลือกรุ่นรถย่อย --</option>
              </select>
            </div>

            <div class="col-md-12 mb-5">
              <label class="form-label">สี</label>
              <select name="color_id[]"
                id="color_id"
                multiple
                class="form-select"
                required>
                @foreach ($gwmColor as $gm)
                <option value="{{ $gm->id }}">
                  {{ $gm->name }}
                </option>
                @endforeach
              </select>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-primary btnStoreColorSub">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>