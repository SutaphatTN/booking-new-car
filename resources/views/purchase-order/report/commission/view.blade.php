<div class="modal fade viewExportCom" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewExportComLabel">ข้อมูล Commission Sales ประจำเดือน</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-sm">
        <form action="{{ route('purchase-order.sale-com-export') }}" method="GET">

          <div class="row">
            <div class="col-6">
              <div class="form-group row mb-1">
                <label for="from_date" class="col-sm-3 col-form-label text-right">จากวันที่ : </label>
                <div class="col-sm-8">
                  <input type="date" id="from_date" name="from_date" class="form-control" />
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group row mb-1">
                <label for="to_date" class="col-sm-3 col-form-label text-right">ถึงวันที่ : </label>
                <div class="col-sm-8">
                  <input type="date" id="to_date" name="to_date" class="form-control" />
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