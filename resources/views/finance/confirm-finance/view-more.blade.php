<div class="modal fade viewFinConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewFinConfirmLabel">ข้อมูลยอดเฟิร์มเงิน FN</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="finance-box">
              <div class="row g-3">
                <div class="col-12">
                  <div class="form-row-item">
                    <label for="customer_fullname" class="form-label">ชื่อ - นามสกุล</label>
                    <input id="customer_fullname" class="form-control" type="text"
                      value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}" disabled />
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="model_id" class="form-label">รุ่นรถหลัก</label>
                    <input id="model_id" type="text"
                      class="form-control"
                      value="{{ $sale->model->Name_TH ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
                    <input id="subModel_id" type="text"
                      class="form-control"
                      value="{{ $sale->subModel->detail ?? '-' }} - {{ $sale->subModel->name ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="option" class="form-label">Option</label>
                    <input id="option" type="text"
                      class="form-control"
                      value="{{ $sale->option ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="Year" class="form-label">ปี</label>
                    <input id="Year" type="text"
                      class="form-control"
                      value="{{ $sale->Year ?? '-' }}" disabled>
                  </div>
                </div>

                @if(auth()->user()->brand == 2)
                <div class="col-12">
                  <div class="form-row-item">
                    <label for="gwm_color" class="form-label">สี / สีภายใน</label>
                    <input id="gwm_color" type="text"
                      class="form-control"
                      value="{{ $sale->gwmColor->name ?? '-' }} / {{ $sale->interiorColor->name ?? '-' }}" disabled>
                  </div>
                </div>
                @else
                <div class="col-12">
                  <div class="form-row-item">
                    <label for="Color" class="form-label">สี</label>
                    <input id="Color" type="text"
                      class="form-control"
                      value="{{ $sale->Color ?? '-' }}" disabled>
                  </div>
                </div>
                @endif

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_alp" class="form-label">ประกัน ALP</label>
                    <input id="view_alp" type="text"
                      class="form-control text-end money-input"
                      value="{{ $sale->remainingPayment->total_alp !== null ? number_format($sale->remainingPayment->total_alp, 2) : '-' }}"
                      disabled>
                  </div>
                </div>

                <!-- <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_net_price" class="form-label">Net Price</label>
                    <input id="view_net_price" type="text"
                      class="form-control text-end money-input"
                      value="{{ $fnCon?->net_price !== null ? number_format($fnCon->net_price, 2) : '-' }}"
                      disabled>
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
                      disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_interest" class="form-label">ดอกเบี้ย</label>
                    <input id="view_interest" type="text"
                      class="form-control"
                      name="interest"
                      value="{{ $sale->remainingPayment->interest . '%' ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_type_com" class="form-label">ประเภทคอม</label>
                    <input id="view_type_com" type="text"
                      class="form-control"
                      name="type_com"
                      value="C{{ $sale->remainingPayment->type_com ?? '-' }}" disabled>
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

                    <input id="view_period" type="text"
                      class="form-control"
                      name="period"
                      value="{{ $sale->remainingPayment->period ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_tax" class="form-label">ภาษีหัก ณ ที่จ่าย</label>
                    <input id="view_tax" type="text"
                      class="form-control"
                      name="tax"
                      value="{{ $sale->remainingPayment?->financeInfo?->tax . '%' ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="date" class="form-label">วันที่ได้รับเงิน</label>
                    <input id="date" type="text"
                      class="form-control"
                      name="date" value="{{ $fnCon->format_date ?? '-' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="firm_date" class="form-label">วันที่เฟิร์มเคส</label>
                    <input id="firm_date" type="text"
                      class="form-control"
                      name="firm_date" value="{{ $fnCon->firm_date ?? '-' }}" disabled>
                  </div>
                </div>

                <!-- <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_down" class="form-label">เงินดาวน์</label>
                    <input id="view_down" type="text"
                      class="form-control text-end"
                      name="down"
                      value="{{ optional($fnCon)->down !== null ? number_format(optional($fnCon)->down, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_excellent" class="form-label">ยอดจัด</label>
                    <input id="view_excellent" type="text"
                      class="form-control text-end"
                      name="excellent"
                      value="{{ optional($fnCon)->excellent !== null ? number_format(optional($fnCon)->excellent, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="com_fin" class="form-label">Com Fin</label>
                    <input id="com_fin" type="text"
                      class="form-control text-end"
                      name="com_fin"
                      value="{{ optional($fnCon)->com_fin !== null ? number_format(optional($fnCon)->com_fin, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="com_extra" class="form-label">Com Extra</label>
                    <input id="com_extra"
                      type="text"
                      class="form-control text-end"
                      name="com_extra"
                      value="{{ optional($fnCon)->com_extra !== null ? number_format(optional($fnCon)->com_extra, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="view_kickback" class="form-label">Com Kickback</label>
                    <input id="view_kickback" name="kickback" type="text"
                      class="form-control text-end money-input"
                      value="{{ $sale->kickback !== null ? number_format($sale->kickback, 2) : '-' }}"
                      disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="com_subsidy" class="form-label">Com Subsidy</label>
                    <input id="com_subsidy" type="text"
                      class="form-control text-end"
                      name="com_subsidy"
                      value="{{ optional($fnCon)->com_subsidy !== null ? number_format(optional($fnCon)->com_subsidy, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="advance_installment" class="form-label">ค่างวดล่วงหน้า</label>
                    <input id="advance_installment" type="text"
                      class="form-control text-end"
                      name="advance_installment"
                      value="{{ optional($fnCon)->advance_installment !== null ? number_format(optional($fnCon)->advance_installment, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="total" class="form-label">สรุปยอด</label>
                    <input id="total" type="text"
                      class="form-control text-end"
                      name="total"
                      value="{{ optional($fnCon)->total !== null ? number_format(optional($fnCon)->total, 2) : '' }}" disabled>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-row-item">
                    <label for="actually_received" class="form-label">ยอดที่ได้รับจริง</label>
                    <input id="actually_received" type="text"
                      class="form-control text-end"
                      name="actually_received"
                      value="{{ optional($fnCon)->actually_received !== null ? number_format(optional($fnCon)->actually_received, 2) : '' }}" disabled>
                  </div>
                </div> -->

              </div>
            </div>
          </div>

        </div>

        <div class="col-12 mt-4">
          <table class="table table-bordered table-sm text-center finance-table">

            <thead class="table-warning">
              <tr>
                <th width="10%">ลำดับ</th>
                  <th width="30%">รายการรับชำระ</th>
                  <th width="20%">ประมาณการ</th>
                  <th width="20%">รับจริง</th>
                  <th width="20%">diff</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td>1</td>
                <td class="text-start">ยอดจ่ายราคารถ</td>
                <td>
                  <input id="view_excellent" type="text"
                    class="form-control text-end"
                    name="excellent"
                    value="{{ optional($fnCon)->excellent !== null ? number_format(optional($fnCon)->excellent, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="excellent_accept" type="text"
                    class="form-control text-end"
                    name="excellent_accept"
                    value="{{ optional($fnCon)->excellent_accept !== null ? number_format(optional($fnCon)->excellent_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="excellent_diff" type="text"
                    class="form-control text-end"
                    name="excellent_diff"
                    value="{{ optional($fnCon)->excellent_diff !== null ? number_format(optional($fnCon)->excellent_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td>2</td>
                <td class="text-start">Com Fin</td>
                <td>
                  <input id="com_fin" type="text"
                    class="form-control text-end"
                    name="com_fin"
                    value="{{ optional($fnCon)->com_fin !== null ? number_format(optional($fnCon)->com_fin, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_fin_accept" type="text"
                    class="form-control text-end"
                    name="com_fin_accept"
                    value="{{ optional($fnCon)->com_fin_accept !== null ? number_format(optional($fnCon)->com_fin_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_fin_diff" type="text"
                    class="form-control text-end"
                    name="com_fin_diff"
                    value="{{ optional($fnCon)->com_fin_diff !== null ? number_format(optional($fnCon)->com_fin_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td>3</td>
                <td class="text-start">Com Extra</td>
                <td>
                  <input id="com_extra"
                    type="text"
                    class="form-control text-end"
                    name="com_extra"
                    value="{{ optional($fnCon)->com_extra !== null ? number_format(optional($fnCon)->com_extra, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_extra_accept" type="text"
                    class="form-control text-end"
                    name="com_extra_accept"
                    value="{{ optional($fnCon)->com_extra_accept !== null ? number_format(optional($fnCon)->com_extra_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_extra_diff" type="text"
                    class="form-control text-end"
                    name="com_extra_diff"
                    value="{{ optional($fnCon)->com_extra_diff !== null ? number_format(optional($fnCon)->com_extra_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td>4</td>
                <td class="text-start">Com Kickback</td>
                <td>
                  <input id="view_kickback" name="kickback" type="text"
                    class="form-control text-end money-input"
                    value="{{ $sale->kickback !== null ? number_format($sale->kickback, 2) : '-' }}"
                    disabled>
                </td>
                <td>
                  <input id="com_kickback_accept" type="text"
                    class="form-control text-end"
                    name="com_kickback_accept"
                    value="{{ optional($fnCon)->com_kickback_accept !== null ? number_format(optional($fnCon)->com_kickback_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_kickback_diff" type="text"
                    class="form-control text-end"
                    name="com_kickback_diff"
                    value="{{ optional($fnCon)->com_kickback_diff !== null ? number_format(optional($fnCon)->com_kickback_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td>5</td>
                <td class="text-start">Com Subsidy</td>
                <td>
                  <input id="com_subsidy" type="text"
                    class="form-control text-end"
                    name="com_subsidy"
                    value="{{ optional($fnCon)->com_subsidy !== null ? number_format(optional($fnCon)->com_subsidy, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_subsidy_accept" type="text"
                    class="form-control text-end"
                    name="com_subsidy_accept"
                    value="{{ optional($fnCon)->com_subsidy_accept !== null ? number_format(optional($fnCon)->com_subsidy_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="com_subsidy_diff" type="text"
                    class="form-control text-end"
                    name="com_subsidy_diff"
                    value="{{ optional($fnCon)->com_subsidy_diff !== null ? number_format(optional($fnCon)->com_subsidy_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td>6</td>
                <td class="text-start">ค่างวดล่วงหน้า</td>
                <td>
                  <input id="advance_installment" type="text"
                    class="form-control text-end"
                    name="advance_installment"
                    value="{{ optional($fnCon)->advance_installment !== null ? number_format(optional($fnCon)->advance_installment, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="advance_installment_accept" type="text"
                    class="form-control text-end"
                    name="advance_installment_accept"
                    value="{{ optional($fnCon)->advance_installment_accept !== null ? number_format(optional($fnCon)->advance_installment_accept, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="advance_installment_diff" type="text"
                    class="form-control text-end"
                    name="advance_installment_diff"
                    value="{{ optional($fnCon)->advance_installment_diff !== null ? number_format(optional($fnCon)->advance_installment_diff, 2) : '' }}" disabled>
                </td>
              </tr>

              <tr>
                <td></td>
                <td class="text-start">รวมเงินทั้งหมด</td>
                <td>
                  <input id="total" type="text"
                    class="form-control text-end"
                    name="total"
                    value="{{ optional($fnCon)->total !== null ? number_format(optional($fnCon)->total, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="actually_received" type="text"
                    class="form-control text-end"
                    name="actually_received"
                    value="{{ optional($fnCon)->actually_received !== null ? number_format(optional($fnCon)->actually_received, 2) : '' }}" disabled>
                </td>
                <td>
                  <input id="diff" type="text"
                    class="form-control text-end"
                    name="diff"
                    value="{{ optional($fnCon)->diff !== null ? number_format(optional($fnCon)->diff, 2) : '' }}" disabled>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

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