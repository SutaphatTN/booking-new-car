<div class="modal fade editFinConfirm" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editFinConfirmLabel">ข้อมูลยอดเฟิร์มเงิน FN</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('purchase-order.updateFN', $sale->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <input type="hidden" id="net_price" value="{{ $fnCon->net_price }}">
            <input type="hidden" id="down" value="{{ $fnCon->down }}">
            <input type="hidden" id="excellent" value="{{ $fnCon->excellent }}">

            <input type="hidden" id="alp" value="{{ $sale->remainingPayment->alp }}">
            <input type="hidden" id="interest" value="{{ $sale->remainingPayment->interest }}">
            <input type="hidden" id="type_com" value="{{ $sale->remainingPayment->type_com }}">
            <input type="hidden" id="period" value="{{ $sale->remainingPayment->period }}">
            <input type="hidden" id="tax" value="{{ $sale->remainingPayment?->financeInfo?->tax }}">

            <div class="col-md-6 mb-5">
              <label for="customer_fullname" class="form-label">ชื่อ - นามสกุล</label>
              <input id="customer_fullname" type="text"
                class="form-control"
                value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}" readonly>
            </div>

            <div class="col-md-6 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <input id="model_id" type="text"
                class="form-control"
                value="{{ $sale->model->Name_TH ?? '-' }}" readonly>
            </div>

            <div class="col-md-6 mb-5">
              <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
              <input id="subModel_id" type="text"
                class="form-control"
                value="{{ $sale->subModel->detail ?? '-' }} - {{ $sale->subModel->name ?? '-' }}" readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="option" class="form-label">Option</label>
              <input id="option" type="text"
                class="form-control"
                value="{{ $sale->option ?? '-' }}" readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="Color" class="form-label">สี</label>
              <input id="Color" type="text"
                class="form-control"
                value="{{ $sale->Color ?? '-' }}" readonly>
            </div>

            <div class="col-md-2 mb-5">
              <label for="Year" class="form-label">ปี</label>
              <input id="Year" type="text"
                class="form-control"
                value="{{ $sale->Year ?? '-' }}" readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="interest_show" class="form-label">ดอกเบี้ย</label>
              <input id="interest_show" type="text" class="form-control"
                value="{{ $sale->remainingPayment?->interest !== null ? $sale->remainingPayment->interest . '%' : '-' }}"
                readonly>
            </div>

            <div class="col-md-4 mb-5">
              <label for="type_com_show" class="form-label">ประเภทคอม</label>
              <input id="type_com_show" type="text" class="form-control"
                value="{{ $sale->remainingPayment?->type_com !== null ? 'C' . $sale->remainingPayment->type_com : '-' }}"
                readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="period_show" class="form-label">จำนวนปีที่ผ่อน</label>

              @php
              $years = $sale->remainingPayment?->period
              ? $sale->remainingPayment->period / 12
              : null;
              @endphp

              <input id="period_show" type="text"
                class="form-control"
                value="{{ $years ?? '-' }}"
                readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="tax_show" class="form-label">ภาษีหัก ณ ที่จ่าย</label>
              <input id="tax_show" type="text" class="form-control"
                value="{{ $sale->remainingPayment?->financeInfo?->tax !== null ? $sale->remainingPayment->financeInfo->tax . '%' : '-' }}"
                readonly>
            </div>

            <div class="col-md-3 mb-5">
              <label for="down_show" class="form-label">เงินดาวน์</label>
              <input id="down_show" type="text"
                class="form-control text-end money-input"
                name="down" value="{{ old('down', $fnCon->down) }}" required>
            </div>

            <div class="col-md-3 mb-5">
              <label for="excellent_show" class="form-label">ยอดจัด</label>
              <input id="excellent_show" type="text"
                class="form-control text-end money-input"
                name="excellent" value="{{ old('excellent', $fnCon->excellent) }}" required>
            </div>


            <div class="col-md-3 mb-5">
              <label for="com_fin" class="form-label">Com Fin</label>
              <input id="com_fin" type="text"
                class="form-control text-end money-input"
                name="com_fin" value="{{ old('com_fin', $fnCon->com_fin) }}" required>
            </div>

            <div class="col-md-3 mb-5">
              <label for="com_extra" class="form-label">Com Extra</label>
              <input id="com_extra"
                type="text"
                class="form-control text-end money-input"
                name="com_extra"
                value="{{ old('com_extra', $fnCon->com_extra ?? $comExtra) }}"
                required>
            </div>

            <div class="col-md-3 mb-5">
              <label for="com_kickback" class="form-label">Com Kickback</label>
              <input id="com_kickback" type="text"
                class="form-control text-end money-input"
                name="com_kickback" value="{{ old('com_kickback', $fnCon->com_kickback) }}">
            </div>

            <div class="col-md-3 mb-5">
              <label for="com_subsidy" class="form-label">Com Subsidy</label>
              <input id="com_subsidy" type="text"
                class="form-control text-end money-input"
                name="com_subsidy" value="{{ old('com_subsidy', $fnCon->com_subsidy) }}">
            </div>

            <div class="col-md-3 mb-5">
              <label for="advance_installment" class="form-label">ค่างวดล่วงหน้า</label>
              <input id="advance_installment" type="text"
                class="form-control text-end money-input"
                name="advance_installment" value="{{ old('advance_installment', $fnCon->advance_installment) }}">
            </div>

            <div class="col-md-4 mb-5">
              <label for="total" class="form-label">สรุปยอด</label>
              <input id="total" type="text"
                class="form-control text-end money-input"
                name="total" value="{{ old('total', $fnCon->total) }}" required>
            </div>

            <div class="col-md-4 mb-5">
              <label for="actually_received" class="form-label">ยอดที่ได้รับจริง</label>
              <input id="actually_received" type="text"
                class="form-control text-end money-input"
                name="actually_received" value="{{ old('actually_received', $fnCon->actually_received) }}">
            </div>

            <div class="col-md-4 mb-5">
              <label for="date" class="form-label">วันที่ได้รับเงิน</label>
              <input id="date" type="date"
                class="form-control"
                name="date" value="{{ old('date', $fnCon->date) }}">
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateFinanceConfirm">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>