<div class="modal fade viewFinConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewFinConfirmLabel">ข้อมูลยอดเฟิร์มเงิน FN</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-5">
            <label for="customer_fullname" class="form-label">ชื่อ - นามสกุล</label>
            <input id="customer_fullname" class="form-control" type="text"
              value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input id="model_id" type="text"
              class="form-control"
              value="{{ $sale->model->Name_TH ?? '-' }}" disabled>
          </div>

          <div class="col-md-8 mb-5">
            <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
            <input id="subModel_id" type="text"
              class="form-control"
              value="{{ $sale->subModel->detail ?? '-' }} - {{ $sale->subModel->name ?? '-' }}" disabled>
          </div>

          <div class="col-md-2 mb-5">
            <label for="option" class="form-label">Option</label>
            <input id="option" type="text"
              class="form-control"
              value="{{ $sale->option ?? '-' }}" disabled>
          </div>

          <div class="col-md-2 mb-5">
            <label for="Year" class="form-label">ปี</label>
            <input id="Year" type="text"
              class="form-control"
              value="{{ $sale->Year ?? '-' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="Color" class="form-label">สี</label>
            <input id="Color" type="text"
              class="form-control"
              value="{{ $sale->Color ?? '-' }}" disabled>
          </div>

          <div class="col-md-5 mb-5">
            <label for="edit_FinanceCompany" class="form-label">ไฟแนนซ์</label>
            <input id="edit_FinanceCompany" type="text"
              class="form-control"
              value="{{ $sale->remainingPayment?->financeInfo?->FinanceCompany ?? '-' }}"
              disabled>
          </div>

          <div class="col-md-2 mb-5">
            <label for="view_interest" class="form-label">ดอกเบี้ย</label>
            <input id="view_interest" type="text"
              class="form-control"
              name="interest"
              value="{{ $sale->remainingPayment->interest . '%' ?? '-' }}" disabled>
          </div>

          <div class="col-md-2 mb-5">
            <label for="view_type_com" class="form-label">ประเภทคอม</label>
            <input id="view_type_com" type="text"
              class="form-control"
              name="type_com"
              value="C{{ $sale->remainingPayment->type_com ?? '-' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="view_period" class="form-label">จำนวนปีที่ผ่อน</label>

            @php
            $years = $sale->remainingPayment?->period
            ? $sale->remainingPayment->period / 12
            : null;
            @endphp

            <input id="view_period" type="text"
              class="form-control"
              name="period"
              value="{{ $years ?? '-' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="view_tax" class="form-label">ภาษีหัก ณ ที่จ่าย</label>
            <input id="view_tax" type="text"
              class="form-control"
              name="tax"
              value="{{ $sale->remainingPayment?->financeInfo?->tax . '%' ?? '-' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="view_net_price" class="form-label">Net Price</label>
            <input id="view_net_price" type="text"
              class="form-control text-end money-input"
              value="{{ $fnCon->net_price !== null ? number_format($fnCon->net_price, 2) : '-' }}"
              disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="view_alp" class="form-label">ค่างวด (กรณีไม่มี ALP)</label>
            <input id="view_alp" type="text"
              class="form-control text-end money-input"
              value="{{ $sale->remainingPayment->alp !== null ? number_format($sale->remainingPayment->alp, 2) : '-' }}"
              disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="view_down" class="form-label">เงินดาวน์</label>
            <input id="view_down" type="text"
              class="form-control text-end"
              name="down"
              value="{{ optional($fnCon)->down !== null ? number_format(optional($fnCon)->down, 2) : '' }}" disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="view_excellent" class="form-label">ยอดจัด</label>
            <input id="view_excellent" type="text"
              class="form-control text-end"
              name="excellent"
              value="{{ optional($fnCon)->excellent !== null ? number_format(optional($fnCon)->excellent, 2) : '' }}" disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="com_fin" class="form-label">Com Fin</label>
            <input id="com_fin" type="text"
              class="form-control text-end"
              name="com_fin"
              value="{{ optional($fnCon)->com_fin !== null ? number_format(optional($fnCon)->com_fin, 2) : '' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="com_extra" class="form-label">Com Extra</label>
            <input id="com_extra"
              type="text"
              class="form-control text-end"
              name="com_extra"
              value="{{ optional($fnCon)->com_extra !== null ? number_format(optional($fnCon)->com_extra, 2) : '' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="com_kickback" class="form-label">Com Kickback</label>
            <input id="com_kickback" type="text"
              class="form-control text-end"
              name="com_kickback"
              value="{{ optional($fnCon)->com_kickback !== null ? number_format(optional($fnCon)->com_kickback, 2) : '' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="com_subsidy" class="form-label">Com Subsidy</label>
            <input id="com_subsidy" type="text"
              class="form-control text-end"
              name="com_subsidy"
              value="{{ optional($fnCon)->com_subsidy !== null ? number_format(optional($fnCon)->com_subsidy, 2) : '' }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="advance_installment" class="form-label">ค่างวดล่วงหน้า</label>
            <input id="advance_installment" type="text"
              class="form-control text-end"
              name="advance_installment"
              value="{{ optional($fnCon)->advance_installment !== null ? number_format(optional($fnCon)->advance_installment, 2) : '' }}" disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="total" class="form-label">สรุปยอด</label>
            <input id="total" type="text"
              class="form-control text-end"
              name="total"
              value="{{ optional($fnCon)->total !== null ? number_format(optional($fnCon)->total, 2) : '' }}" disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="actually_received" class="form-label">ยอดที่ได้รับจริง</label>
            <input id="actually_received" type="text"
              class="form-control text-end"
              name="actually_received"
              value="{{ optional($fnCon)->actually_received !== null ? number_format(optional($fnCon)->actually_received, 2) : '' }}" disabled>
          </div>

          <div class="col-md-4 mb-5">
            <label for="date" class="form-label">วันที่ได้รับเงิน</label>
            <input id="date" type="text"
              class="form-control"
              name="date" value="{{ $fnCon->format_date ?? '-' }}" disabled>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>