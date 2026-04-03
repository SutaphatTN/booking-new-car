<div class="modal fade viewExportMonthlyDelivery" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewExportMonthlyDeliveryLabel">รายงานส่งมอบประจำเดือน</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-sm">
        <form action="{{ route('purchase-order.monthlyDelivery-export') }}" method="GET">

          <div class="row">
            <div class="col-12">
              <div class="form-group row mb-2">
                <label class="col-sm-4 col-form-label text-right">ประเภท : </label>
                <div class="col-sm-8">
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="radio" name="date_type" id="type_dms" value="dms" checked>
                    <label class="form-check-label" for="type_dms">วันส่งมอบบริษัท</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="date_type" id="type_ck" value="ck">
                    <label class="form-check-label" for="type_ck">วันส่งมอบของเซลล์</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group row mb-1">
                <label for="from_date" class="col-sm-4 col-form-label text-right">เลือกเดือน : </label>
                <div class="col-sm-8">
                  <input type="month" id="from_date" name="from_date" class="form-control" />
                </div>
              </div>
            </div>
          </div>

          <br>
          <div class="text-center">
            <button type="submit" class="btn bg-success text-white" style="margin-right: 3px;">
              <i class="bx bxs-file"></i>&nbsp;Export
            </button>
            <button type="button" class="btn bg-danger text-white" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> ยกเลิก
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
