<div class="modal fade viewExportGP" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewExportGPLabel">ข้อมูล GP ประจำเดือน</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-sm">
        <form action="{{ route('purchase-order.gp-export') }}" method="GET">

          <div class="row">
            <div class="col-12">
              <div class="form-group row mb-1">
                <label for="from_date" class="col-sm-3 col-form-label text-right">เลือกเดือน : </label>
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