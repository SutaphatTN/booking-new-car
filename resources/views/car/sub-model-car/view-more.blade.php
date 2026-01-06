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
            <input id="model_id" class="form-control" type="text" value="{{ $sub->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label for="name" class="form-label">ชื่อรุ่นรถย่อย</label>
            <input id="name" class="form-control" type="text" value="{{ $sub->name }}" autocomplete="off" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label for="detail" class="form-label">รายละเอียด</label>
            <textarea id="detail" name="detail" class="form-control" disabled>{{ $sub->detail ?: '-' }}</textarea>
          </div>

          <!-- <div class="col-md-6 mb-5">
            <label for="year" class="form-label">ปี</label>
            <input id="year" class="form-control" type="text" value="{{ $sub->year ?? '-' }}" autocomplete="off" disabled />
          </div> -->

          <div class="col-md-12 mb-5">
            <label for="over_budget" class="form-label">ยอดเงินเกินงบ</label>
            <input id="over_budget" class="form-control text-end" type="text" 
            value="{{ $sub->over_budget !== null ? number_format($sub->over_budget, 2) : '-' }}"
            autocomplete="off" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>