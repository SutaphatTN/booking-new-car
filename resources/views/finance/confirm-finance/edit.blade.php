<div class="modal fade editFinConfirm" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขยอดเฟิร์มเงิน FN</h6>
            <small class="text-white mf-hd-sub">
              {{ $sale->customer->prefix->Name_TH ?? '' }}
              {{ $sale->customer->FirstName ?? '' }}
              {{ $sale->customer->LastName ?? '' }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('purchase-order.updateFN', $sale->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Hidden fields --}}
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

          <div class="row g-3 mb-3">

            {{-- Section 1 : ข้อมูลรถ --}}
            <div class="col-md-6">
              <div class="mf-section h-100 mb-0">
                <div class="mf-section-hd">
                  <div class="mf-section-icon sky">
                    <i class="bx bx-car"></i>
                  </div>
                  <span class="mf-section-title">ข้อมูลรถ</span>
                </div>
                <div class="mf-section-body">
                  <div class="row g-3">

                    <div class="col-12">
                      <label for="customer_fullname" class="mf-label form-label">
                        <i class="bx bx-user ci-sky"></i> ชื่อ - นามสกุล
                      </label>
                      <input id="customer_fullname" type="text" class="form-control"
                        value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-12">
                      <label for="model_id" class="mf-label form-label">
                        <i class="bx bx-car ci-sky"></i> รุ่นรถหลัก
                      </label>
                      <input id="model_id" type="text" class="form-control"
                        value="{{ $sale->model->Name_TH ?? '-' }}" style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-12">
                      <label for="subModel_id" class="mf-label form-label">
                        <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                      </label>
                      <input id="subModel_id" type="text" class="form-control"
                        value="{{ !empty($sale->subModel) ? ($sale->subModel->detail ? $sale->subModel->detail . ' - ' . $sale->subModel->name : $sale->subModel->name) : '' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="option" class="mf-label form-label">
                        <i class="bx bx-list-check ci-sky"></i> Option
                      </label>
                      <input id="option" type="text" class="form-control" value="{{ $sale->option ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="Year" class="mf-label form-label">
                        <i class="bx bx-calendar ci-sky"></i> ปี
                      </label>
                      <input id="Year" type="text" class="form-control" value="{{ $sale->Year ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    @if (auth()->user()->brand == 2)
                      <div class="col-7">
                        <label for="gwm_color" class="mf-label form-label">
                          <i class="bx bx-palette ci-sky"></i> สี / สีภายใน
                        </label>
                        <input id="gwm_color" type="text" class="form-control"
                          value="{{ $sale->gwmColor->name ?? '-' }} / {{ $sale->interiorColor->name ?? '-' }}"
                          style="background:#f8fafc;color:#64748b;" readonly>
                      </div>
                    @else
                      <div class="col-7">
                        <label for="Color" class="mf-label form-label">
                          <i class="bx bx-palette ci-sky"></i> สี
                        </label>
                        <input id="Color" type="text" class="form-control" value="{{ $sale->Color ?? '-' }}"
                          style="background:#f8fafc;color:#64748b;" readonly>
                      </div>
                    @endif

                    <div class="col-5">
                      <label for="edit_alp" class="mf-label form-label">
                        <i class="bx bx-shield-quarter ci-sky"></i> ประกัน ALP
                      </label>
                      <div class="input-group">
                        <span class="input-group-text ig-sky">฿</span>
                        <input id="edit_alp" type="text" class="form-control text-end money-input"
                          value="{{ $sale->remainingPayment->total_alp ?? '' }}"
                          style="background:#f8fafc;color:#64748b;" readonly>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            {{-- Section 2 : ข้อมูลไฟแนนซ์ --}}
            <div class="col-md-6">
              <div class="mf-section h-100 mb-0">
                <div class="mf-section-hd">
                  <div class="mf-section-icon indigo">
                    <i class="bx bx-building"></i>
                  </div>
                  <span class="mf-section-title">ข้อมูลไฟแนนซ์</span>
                </div>
                <div class="mf-section-body">
                  <div class="row g-3">

                    <div class="col-12">
                      <label for="edit_FinanceCompany" class="mf-label form-label">
                        <i class="bx bx-building ci-indigo"></i> ไฟแนนซ์
                      </label>
                      <input id="edit_FinanceCompany" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->financeInfo?->FinanceCompany ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="edit_interest" class="mf-label form-label">
                        <i class="bx bx-trending-up ci-indigo"></i> ดอกเบี้ย
                      </label>
                      <input id="edit_interest" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->interest !== null ? $sale->remainingPayment->interest . '%' : '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="edit_tax" class="mf-label form-label">
                        <i class="bx bx-receipt ci-indigo"></i> ภาษีหัก ณ ที่จ่าย
                      </label>
                      <input id="edit_tax" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->financeInfo?->tax !== null ? $sale->remainingPayment->financeInfo->tax . '%' : '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="edit_type_com" class="mf-label form-label">
                        <i class="bx bx-category ci-indigo"></i> ประเภทคอม
                      </label>
                      <input id="edit_type_com" type="text" class="form-control"
                        value="{{ $sale->remainingPayment?->type_com !== null ? 'C' . $sale->remainingPayment->type_com : '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-6">
                      <label for="edit_period" class="mf-label form-label">
                        <i class="bx bx-time ci-indigo"></i> จำนวนเดือนที่ผ่อน
                      </label>
                      @php $years = $sale->remainingPayment?->period ? $sale->remainingPayment->period / 12 : null; @endphp
                      <input id="edit_period" type="text" class="form-control"
                        value="{{ $sale->remainingPayment->period ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" readonly>
                    </div>

                    <div class="col-md-6">
                      <label for="date" class="mf-label form-label">
                        <i class="bx bx-calendar-check ci-indigo"></i> วันที่ได้รับเงิน
                      </label>
                      <input id="date" type="date" class="form-control" name="date"
                        value="{{ old('date', $fnCon->date) }}">
                    </div>

                    <div class="col-md-6">
                      <label for="firm_date" class="mf-label form-label">
                        <i class="bx bx-calendar-event ci-indigo"></i> วันที่เฟิร์มเคส
                      </label>
                      <input id="firm_date" type="date" class="form-control" name="firm_date"
                        value="{{ old('firm_date', $fnCon->firm_date) }}">
                    </div>

                  </div>
                </div>
              </div>
            </div>

          </div>

          {{-- Section 3 : ยอดรับชำระ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ยอดรับชำระ</span>
            </div>
            <div class="mf-section-body p-0">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" style="font-size:.83rem; min-width:900px;">
                  <thead class="fn-thead">
                    <tr style="background:#fef3c7;border-bottom:2px solid #d97706;">
                      <th style="width:80px;">ลำดับ</th>
                      <th style="min-width:220px;">รายการรับชำระ</th>
                      <th style="min-width:160px;">ประมาณการ</th>
                      <th style="min-width:160px;">รับจริง</th>
                      <th style="min-width:160px;">diff</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">1</td>
                      <td class="fw-semibold">ยอดจ่ายราคารถ</td>
                      <td><input id="edit_excellent" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="excellent" value="{{ old('excellent', $fnCon->excellent) }}" readonly></td>
                      <td><input id="excellent_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="excellent_accept"
                          value="{{ number_format($fnCon->excellent_accept ?? 0, 2) }}"></td>
                      <td><input id="excellent_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="excellent_diff" readonly></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">2</td>
                      <td class="fw-semibold">Com Fin</td>
                      <td><input id="com_fin" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_fin" value="{{ old('com_fin', $fnCon->com_fin) }}" readonly></td>
                      <td><input id="com_fin_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="com_fin_accept"
                          value="{{ number_format($fnCon->com_fin_accept ?? 0, 2) }}"></td>
                      <td><input id="com_fin_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_fin_diff" readonly></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">3</td>
                      <td class="fw-semibold">Com Extra</td>
                      <td><input id="com_extra" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_extra" value="{{ old('com_extra', $fnCon->com_extra ?? $comExtra) }}" readonly>
                      </td>
                      <td><input id="com_extra_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="com_extra_accept"
                          value="{{ number_format($fnCon->com_extra ?? 0, 2) }}"></td>
                      <td><input id="com_extra_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_extra_diff" readonly></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">4</td>
                      <td class="fw-semibold">Com Kickback</td>
                      <td>
                        <input type="hidden" name="com_kickback" id="com_kickback" value="{{ $sale->kickback }}">
                        <input id="edit_kickback" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          value="{{ $sale->kickback !== null ? $sale->kickback : '-' }}" readonly>
                      </td>
                      <td><input id="com_kickback_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="com_kickback_accept"
                          value="{{ number_format($fnCon->com_kickback_accept ?? 0, 2) }}"></td>
                      <td><input id="com_kickback_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_kickback_diff" readonly></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">5</td>
                      <td class="fw-semibold">Com Subsidy</td>
                      <td><input id="com_subsidy" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="com_subsidy"
                          value="{{ number_format($fnCon->com_subsidy ?? 0, 2) }}"></td>
                      <td><input id="com_subsidy_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="com_subsidy_accept"
                          value="{{ number_format($fnCon->com_subsidy_accept ?? 0, 2) }}"></td>
                      <td><input id="com_subsidy_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="com_subsidy_diff" readonly></td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="text-center text-muted">6</td>
                      <td class="fw-semibold">ค่างวดล่วงหน้า</td>
                      <td><input id="advance_installment" type="text"
                          class="form-control form-control-sm text-end money-decimal" name="advance_installment"
                          value="{{ number_format($fnCon->advance_installment ?? 0, 2) }}"></td>
                      <td><input id="advance_installment_accept" type="text"
                          class="form-control form-control-sm text-end money-decimal"
                          name="advance_installment_accept"
                          value="{{ number_format($fnCon->advance_installment_accept ?? 0, 2) }}"></td>
                      <td><input id="advance_installment_diff" type="text"
                          class="form-control form-control-sm text-end money-input form-control-plaintext-mf"
                          name="advance_installment_diff" readonly></td>
                    </tr>
                    <tr style="background:#fffbeb;border-top:2px solid #d97706;">
                      <td></td>
                      <td class="fw-bold">รวมเงินทั้งหมด</td>
                      <td><input id="total" type="text"
                          class="form-control form-control-sm text-end fw-semibold money-input"
                          style="background:#fef9ec;border-color:#fde68a;" name="total"
                          value="{{ old('total', $fnCon->total ?? '') }}" readonly></td>
                      <td><input id="actually_received" type="text"
                          class="form-control form-control-sm text-end fw-semibold money-decimal"
                          style="background:#fef9ec;border-color:#fde68a;" name="actually_received"
                          value="{{ number_format($fnCon->actually_received ?? 0, 2) }}"></td>
                      <td><input id="diff" type="text"
                          class="form-control form-control-sm text-end fw-semibold money-input"
                          style="background:#fef9ec;border-color:#fde68a;" name="diff"
                          value="{{ old('diff', $fnCon->diff) }}" readonly></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateFinanceConfirm">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
