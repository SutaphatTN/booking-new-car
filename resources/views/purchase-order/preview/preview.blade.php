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
        // อนุมัติแล้วหรือยัง (brand-aware ให้ตรงกับ approvalCase/isApproved ใน controller)
        $__balance = (float) ($saleCar->balanceCampaign ?? 0);
        $__brand = (int) $saleCar->brand;
        if ($__balance >= 0) {
            // งบปกติ → manager (SMSignature)
            $hasApproval = (bool) $saleCar->SMSignature;
        } elseif ($__brand === 2 || $__brand === 3) {
            // brand 2: gm/md, brand 3: md → จบที่ GMApprovalSignature
            $hasApproval = (bool) $saleCar->GMApprovalSignature;
        } else {
            // brand 1: เกิน ≤ over_budget → manager(ApprovalSignature) / เกิน > over_budget → md(GMApprovalSignature)
            $__overBudget = (float) ($saleCar->model?->over_budget ?? 0);
            $hasApproval = abs($__balance) <= $__overBudget
                ? (bool) $saleCar->ApprovalSignature
                : (bool) $saleCar->GMApprovalSignature;
        }
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

<style>
  /* มือถือ: ปุ่ม footer เรียงเต็มความกว้าง ไม่ล้นออกนอก modal */
  @media (max-width: 575.98px) {
    #previewPurchase .modal-footer {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    #previewPurchase .modal-footer .btn {
      width: 100%;
      margin: 0;
    }
  }
</style>
