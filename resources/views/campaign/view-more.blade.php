<div class="modal fade viewCam" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewCamLabel">ข้อมูลแคมเปญ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input class="form-control" type="text" value="{{ $cam->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
            <input class="form-control" type="text" value="{{ $cam->subModel->name }}" disabled />
          </div>

          <div class="col-md-7 mb-5">
            <label for="name" class="form-label">ชื่อแคมเปญ</label>
            <input class="form-control" type="text" value="{{ $cam->name }}" disabled />
          </div>

          <div class="col-md-5 mb-5">
            <label for="campaign_type" class="form-label">ประเภท</label>
            <input class="form-control" type="text" value="{{ $cam->type->name }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="cashSupport" class="form-label">เงินการขาย</label>
            <input class="form-control text-end" type="text" value="{{ $cam->cashSupport }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="cashSupport_deduct" class="form-label">เงินหัก</label>
            <input class="form-control text-end" type="text" value="{{ $cam->cashSupport_deduct }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="cashSupport_final" class="form-label">จำนวนเงินที่เหลือ</label>
            <input class="form-control text-end" type="text" value="{{ $cam->cashSupport_final }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="startDate" class="form-label">วันที่เริ่ม</label>
            <input class="form-control" type="text" value="{{ $cam->format_start_date }}" disabled />
          </div>
          <div class="col-md-6 mb-5">
            <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
            <input class="form-control" type="text" value="{{ $cam->format_end_date }}" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>