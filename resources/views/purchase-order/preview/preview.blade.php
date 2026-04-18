<div class="modal fade" id="previewPurchase" tabindex="-1" aria-labelledby="previewPurchaseLabel" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-search-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="previewPurchaseLabel">Preview ข้อมูล ก่อนบันทึก</h6>
            <small class="text-white mf-hd-sub">Purchase Order Preview</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body" id="previewPurchaseContent">
      </div>

      @php
        $hasApproval = $saleCar->ApprovalSignature || $saleCar->GMApprovalSignature;
      @endphp

      <div class="modal-footer mt-4">
        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>ปิด
        </button>

        <button type="submit"
          class="btn btn-primary px-5 d-none"
          id="btnUpdatePurchase"
          form="purchaseForm">
          <i class="bx bx-save me-1"></i>บันทึก
        </button>

        <button type="button"
          class="btn btn-warning px-4 d-none"
          id="btnRequestNormal">
          <i class="bx bx-check-circle me-1"></i>ขออนุมัติ
        </button>

        <button type="button"
          class="btn btn-danger px-4 d-none"
          id="btnRequestOverBudget">
          <i class="bx bx-error-circle me-1"></i>ขออนุมัติเกินงบ
        </button>

        <input type="hidden" id="userRole" value="{{ $userRole }}">
        <input type="hidden" id="hasApproval" value="{{ $hasApproval ? 1 : 0 }}">
        <input type="hidden" name="action_type" id="action_type" value="">
      </div>

    </div>
  </div>
</div>
