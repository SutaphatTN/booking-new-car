<div class="modal fade editFinConfirm" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
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

          <div class="row g-4">
            <input type="hidden" id="net_price" name="net_price" value="{{ $fnCon->net_price }}">

            <input type="hidden" id="down" value="{{ $fnCon->down }}">
            <input type="hidden" id="excellent" value="{{ $fnCon->excellent }}">

            <input type="hidden" id="total_alp" value="{{ $sale->remainingPayment->total_alp }}">
            <input type="hidden" id="interest" value="{{ $sale->remainingPayment->interest }}">
            <input type="hidden" id="type_com" value="{{ $sale->remainingPayment->type_com }}">
            <input type="hidden" id="period" value="{{ $sale->remainingPayment->period }}">
            <input type="hidden" id="tax" value="{{ $sale->remainingPayment?->financeInfo?->tax }}">
            <input type="hidden" id="kickback" value="{{ $sale->kickback }}">
            <input type="hidden" id="max_year" value="{{ $maxYear }}">

            <div class="col-md-6">
              <div class="finance-box">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="customer_fullname" class="form-label">ชื่อ - นามสกุล</label>
                      <input id="customer_fullname" type="text"
                        class="form-control"
                        value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}" readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="model_id" class="form-label">รุ่นรถหลัก</label>
                      <input id="model_id" type="text"
                        class="form-control"
                        value="{{ $sale->model->Name_TH ?? '-' }}" readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
                      <input id="subModel_id" type="text"
                        class="form-control"
                        value="{{ $sale->subModel->detail ?? '-' }} - {{ $sale->subModel->name ?? '-' }}" readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="option" class="form-label">Option</label>
                      <input id="option" type="text"
                        class="form-control"
                        value="{{ $sale->option ?? '-' }}" readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="Year" class="form-label">ปี</label>
                      <input id="Year" type="text"
                        class="form-control"
                        value="{{ $sale->Year ?? '-' }}" readonly>
                    </div>
                  </div>

                  @if(auth()->user()->brand == 2)
                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="gwm_color" class="form-label">สี / สีภายใน</label>
                      <input id="gwm_color" type="text"
                        class="form-control"
                        value="{{ $sale->gwmColor->name ?? '-' }} / {{ $sale->interiorColor->name ?? '-' }}" readonly>
                    </div>
                  </div>
                  @else
                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="Color" class="form-label">สี</label>
                      <input id="Color" type="text"
                        class="form-control"
                        value="{{ $sale->Color ?? '-' }}" readonly>
                    </div>
                  </div>
                  @endif

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_alp" class="form-label">ประกัน ALP</label>
                      <input id="edit_alp" type="text"
                        class="form-control text-end money-input"
                        value="{{ $sale->remainingPayment->total_alp ?? '' }}"
                        readonly>
                    </div>
                  </div>

                  <!-- <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_net_price" class="form-label">Net Price</label>
                      <input id="edit_net_price" type="text"
                        class="form-control text-end money-input"
                        value="{{ $fnCon->net_price ?? '' }}"
                        readonly>
                    </div>
                  </div> -->

                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="finance-box">
                <div class="row g-3">

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="edit_FinanceCompany" class="form-label">ไฟแนนซ์</label>
                      <input id="edit_FinanceCompany" type="text"
                        class="form-control"
                        value="{{ $sale->remainingPayment?->financeInfo?->FinanceCompany ?? '-' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_interest" class="form-label">ดอกเบี้ย</label>
                      <input id="edit_interest" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->interest !== null ? $sale->remainingPayment->interest . '%' : '-' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_type_com" class="form-label">ประเภทคอม</label>
                      <input id="edit_type_com" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->type_com !== null ? 'C' . $sale->remainingPayment->type_com : '-' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_period" class="form-label">จำนวนเดือนที่ผ่อน</label>

                      @php
                      $years = $sale->remainingPayment?->period
                      ? $sale->remainingPayment->period / 12
                      : null;
                      @endphp

                      <input id="edit_period" type="text"
                        class="form-control"
                        value="{{ $sale->remainingPayment->period ?? '-' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_tax" class="form-label">ภาษีหัก ณ ที่จ่าย</label>
                      <input id="edit_tax" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->financeInfo?->tax !== null ? $sale->remainingPayment->financeInfo->tax . '%' : '-' }}"
                        readonly>
                    </div>
                  </div>



                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="date" class="form-label">วันที่ได้รับเงิน</label>
                      <input id="date" type="date"
                        class="form-control"
                        name="date" value="{{ old('date', $fnCon->date) }}">
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="firm_date" class="form-label">วันที่เฟิร์มเคส</label>
                      <input id="firm_date" type="date"
                        class="form-control"
                        name="firm_date" value="{{ old('firm_date', $fnCon->firm_date) }}">
                    </div>
                  </div>

                  <!-- <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_down" class="form-label">เงินดาวน์</label>
                      <input id="edit_down" type="text"
                        class="form-control text-end money-input"
                        name="down" value="{{ old('down', $fnCon->down) }}" required>
                    </div>
                  </div> 

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_excellent" class="form-label">ยอดจัด</label>
                      <input id="edit_excellent" type="text"
                        class="form-control text-end money-input"
                        name="excellent" value="{{ old('excellent', $fnCon->excellent) }}" required>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="com_fin" class="form-label">Com Fin</label>
                      <input id="com_fin" type="text"
                        class="form-control text-end money-input"
                        name="com_fin" value="{{ old('com_fin', $fnCon->com_fin) }}" required>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="com_extra" class="form-label">Com Extra</label>
                      <input id="com_extra"
                        type="text"
                        class="form-control text-end money-input"
                        name="com_extra"
                        value="{{ old('com_extra', $fnCon->com_extra ?? $comExtra) }}"
                        required>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="view_kickback" class="form-label">Com Kickback</label>
                      <input id="edit_kickback" type="text"
                        class="form-control text-end money-input"
                        value="{{ $sale->kickback !== null ? $sale->kickback : '-' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="com_subsidy" class="form-label">Com Subsidy</label>
                      <input id="com_subsidy" type="text"
                        class="form-control text-end money-input"
                        name="com_subsidy" value="{{ old('com_subsidy', $fnCon->com_subsidy ?? '' ) }}">
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="advance_installment" class="form-label">ค่างวดล่วงหน้า</label>
                      <input id="advance_installment" type="text"
                        class="form-control text-end money-input"
                        name="advance_installment" value="{{ old('advance_installment', $fnCon->advance_installment ?? '' ) }}">
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="total" class="form-label">สรุปยอด</label>
                      <input id="total" type="text"
                        class="form-control text-end money-input"
                        name="total" value="{{ old('total', $fnCon->total ?? '' ) }}" required>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-row-item">
                      <label for="actually_received" class="form-label">ยอดที่ได้รับจริง</label>
                      <input id="actually_received" type="text"
                        class="form-control text-end money-input"
                        name="actually_received" value="{{ old('actually_received', $fnCon->actually_received ?? '' ) }}">
                    </div>
                  </div>-->

                </div>
              </div>
            </div>

          </div>

          <div class="col-12 mt-4">
            <table class="table table-bordered table-sm text-center finance-table">

              <thead class="table-warning">
                <tr>
                  <th width="10%">ลำดับ</th>
                  <th width="65%">รายการรับชำระ</th>
                  <th width="25%">ประมาณการ</th>
                </tr>
              </thead>

              <tbody>
                <tr>
                  <td>1</td>
                  <td class="text-start">ยอดจ่ายราคารถ</td>
                  <td>
                    <input id="edit_excellent" type="text"
                      class="form-control text-end money-input"
                      name="excellent" value="{{ old('excellent', $fnCon->excellent) }}" readonly>
                  </td>
                </tr>

                <tr>
                  <td>2</td>
                  <td class="text-start">Com Fin</td>
                  <td>
                    <input id="com_fin" type="text"
                      class="form-control text-end money-input"
                      name="com_fin" value="{{ old('com_fin', $fnCon->com_fin) }}" readonly>
                  </td>
                </tr>

                <tr>
                  <td>3</td>
                  <td class="text-start">Com Extra</td>
                  <td>
                    <input id="com_extra"
                      type="text"
                      class="form-control text-end money-input"
                      name="com_extra"
                      value="{{ old('com_extra', $fnCon->com_extra ?? $comExtra) }}" readonly>
                  </td>
                </tr>

                <tr>
                  <td>4</td>
                  <td class="text-start">Com Kickback</td>
                  <td>
                    <input id="edit_kickback" type="text"
                      class="form-control text-end money-input"
                      value="{{ $sale->kickback !== null ? $sale->kickback : '-' }}" readonly>
                  </td>
                </tr>

                <tr>
                  <td>5</td>
                  <td class="text-start">Com Subsidy</td>
                  <td>
                    <input id="com_subsidy" type="text"
                      class="form-control text-end money-input"
                      name="com_subsidy" value="{{ old('com_subsidy', $fnCon->com_subsidy ?? '' ) }}">
                  </td>
                </tr>

                <tr>
                  <td>6</td>
                  <td class="text-start">ค่างวดล่วงหน้า</td>
                  <td>
                    <input id="advance_installment" type="text"
                      class="form-control text-end money-input"
                      name="advance_installment" value="{{ old('advance_installment', $fnCon->advance_installment ?? '' ) }}">
                  </td>
                </tr>

                <tr class="table-light fw-bold">
                  <td></td>
                  <td class="text-start">รวมเงินทั้งหมด</td>
                  <td>
                    <input id="total" type="text"
                      class="form-control text-end money-input"
                      name="total" value="{{ old('total', $fnCon->total ?? '' ) }}" readonly>
                  </td>
                </tr>

                <tr>
                  <td></td>
                  <td class="text-start">ยอดที่ได้รับจริง</td>
                  <td>
                    <input id="actually_received" type="text"
                      class="form-control text-end money-input"
                      name="actually_received" value="{{ old('actually_received', $fnCon->actually_received ?? '' ) }}">
                  </td>
                </tr>

                <tr class="table-secondary fw-bold">
                  <td></td>
                  <td class="text-start">Diff</td>
                  <td>
                    <input id="diff" type="text"
                      class="form-control text-end money-input"
                      name="diff" value="{{ old('diff', $fnCon->diff) }}" readonly>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateFinanceConfirm">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .form-row-item {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .form-row-item label {
    width: 180px;
    margin-bottom: 0;
    white-space: nowrap;
  }

  .form-row-item .form-control,
  .form-row-item .form-select {
    flex: 1;
  }

  @media (max-width: 768px) {

    .form-row-item {
      flex-direction: column;
      align-items: stretch;
      gap: 4px;
    }

    .form-row-item label {
      width: 100%;
      white-space: normal;
      font-weight: 600;
    }
  }

  .finance-box {
    border: 1px solid #dcdcdc;
    border-radius: 10px;
    padding: 20px 18px;
    height: 100%;
  }

  .finance-table td,
  .finance-table th {
    padding: 10px 12px !important;
    vertical-align: middle;
  }

  .finance-table .form-control {
    height: 32px;
    padding: 4px 8px;
    font-size: 0.9rem;
  }

  .finance-table tbody tr {
    height: 38px;
  }
</style>