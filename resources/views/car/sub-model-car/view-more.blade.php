<div class="modal fade viewSubCar" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewSubCarLabel">ข้อมูลรุ่นรถย่อย</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input class="form-control" type="text" value="{{ $sub->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label for="name" class="form-label">ชื่อรุ่นรถย่อย</label>
            <input class="form-control" type="text" value="{{ $sub->name }}" disabled />
          </div>

          <!-- <div class="col-md-8 mb-5">
            <label for="code" class="form-label">รหัสรถ</label>
            <input class="form-control" type="text" value="{{ $sub->code }}" disabled />
          </div> -->

          <div class="col-md-12 mb-5">
            <label for="detail" class="form-label">รายละเอียด</label>
            <textarea name="detail" class="form-control" disabled>{{ $sub->detail ?: '-' }}</textarea>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>