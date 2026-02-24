<div class="modal fade" id="previewPurchase" tabindex="-1" aria-labelledby="previewPurchaseLabel" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="previewPurchaseLabel">Preview ข้อมูล ก่อนบันทึก</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="previewPurchaseContent">
      </div>

      @php
      $hasApproval = $saleCar->ApprovalSignature || $saleCar->GMApprovalSignature;
      @endphp

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>

        <button type="submit"
          class="btn btn-primary d-none"
          id="btnUpdatePurchase"
          form="purchaseForm">
          บันทึก
        </button>

        <button type="button"
          class="btn btn-warning d-none"
          id="btnRequestNormal">
          ขออนุมัติ
        </button>

        <button type="button"
          class="btn btn-danger d-none"
          id="btnRequestOverBudget">
          ขออนุมัติเกินงบ
        </button>

        <input type="hidden" id="userRole" value="{{ $userRole }}">
        <input type="hidden" id="hasApproval" value="{{ $hasApproval ? 1 : 0 }}">
        <input type="hidden" name="action_type" id="action_type" value="">
        <input type="hidden" name="reason_campaign" id="reason_campaign">

        <input type="hidden" id="approvalRequested"
          value="{{ $saleCar->approval_requested_at ? 1 : 0 }}">

        <input type="hidden" id="approvalType"
          value="{{ $saleCar->approval_type ?? '' }}">

      </div>

    </div>
  </div>
</div>