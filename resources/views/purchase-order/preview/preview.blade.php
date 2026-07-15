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

      {{-- หมายเหตุ: "อนุมัติแล้วหรือยัง" ไม่คำนวณฝั่ง PHP อีกต่อไป —
           JS คำนวณสดจากค่าในฟอร์ม (balanceCampaign + รุ่นรถ + ลายเซ็นด้านล่าง)
           เพื่อจับเคสที่ผู้ใช้แก้ข้อมูลจนเคสอนุมัติเปลี่ยน แต่ยังไม่ได้บันทึก --}}

      <div class="modal-footer mt-4">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
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

        {{-- ดึงคำขอกลับ (เฉพาะ admin) — โชว์ตอนสถานะ "รออนุมัติ" ที่ยังไม่อนุมัติ --}}
        <button type="button"
          class="btn btn-outline-danger px-4 d-none"
          id="btnWithdrawApproval"
          data-id="{{ $saleCar->id }}">
          <i class="bx bx-undo me-1"></i>ดึงคำขอกลับ
        </button>

        <input type="hidden" id="userRole" value="{{ $userRole }}">
        {{-- ประเภทการขาย = Dealer → ไม่ต้องขออนุมัติ (JS เทียบกับค่าที่เลือกใน #type_sale) --}}
        <input type="hidden" id="dealerTypeSaleId" value="{{ \App\Models\Salecar::TYPE_SALE_DEALER }}">
        {{-- ลายเซ็นอนุมัติแต่ละขั้น + ประเภทคำขอเดิม — ให้ JS ประเมิน "อนุมัติตรงกับข้อมูลปัจจุบันไหม" แบบสด
             (กันเคส: อนุมัติงบปกติแล้ว แก้ข้อมูลจนเกินงบ → ต้องขออนุมัติเกินงบใหม่) --}}
        <input type="hidden" id="smSignature" value="{{ $saleCar->SMSignature ? 1 : 0 }}">
        <input type="hidden" id="approvalSignature" value="{{ $saleCar->ApprovalSignature ? 1 : 0 }}">
        <input type="hidden" id="gmApprovalSignature" value="{{ $saleCar->GMApprovalSignature ? 1 : 0 }}">
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
