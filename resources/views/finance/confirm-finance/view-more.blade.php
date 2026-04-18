<div class="modal fade viewFinConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bxs-bank fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ยอดเฟิร์มเงิน FN</h6>
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
                    <input id="customer_fullname" class="form-control" type="text"
                      value="{{ $sale->customer->prefix->Name_TH ?? '' }} {{ $sale->customer->FirstName ?? '-' }} {{ $sale->customer->LastName ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-12">
                    <label for="model_id" class="mf-label form-label">
                      <i class="bx bx-car ci-sky"></i> รุ่นรถหลัก
                    </label>
                    <input id="model_id" type="text" class="form-control" value="{{ $sale->model->Name_TH ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-12">
                    <label for="subModel_id" class="mf-label form-label">
                      <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                    </label>
                    <input id="subModel_id" type="text" class="form-control"
                      value="{{ !empty($sale->subModel) ? ($sale->subModel->detail ? $sale->subModel->detail . ' - ' . $sale->subModel->name : $sale->subModel->name) : '' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-sky"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control" value="{{ $sale->option ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="Year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="Year" type="text" class="form-control" value="{{ $sale->Year ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  @if (auth()->user()->brand == 2)
                    <div class="col-7">
                      <label for="gwm_color" class="mf-label form-label">
                        <i class="bx bx-palette ci-sky"></i> สี / สีภายใน
                      </label>
                      <input id="gwm_color" type="text" class="form-control"
                        value="{{ $sale->gwmColor->name ?? '-' }} / {{ $sale->interiorColor->name ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" disabled>
                    </div>
                  @else
                    <div class="col-7">
                      <label for="Color" class="mf-label form-label">
                        <i class="bx bx-palette ci-sky"></i> สี
                      </label>
                      <input id="Color" type="text" class="form-control" value="{{ $sale->Color ?? '-' }}"
                        style="background:#f8fafc;color:#64748b;" disabled>
                    </div>
                  @endif

                  <div class="col-5">
                    <label for="view_alp" class="mf-label form-label">
                      <i class="bx bx-shield-quarter ci-sky"></i> ประกัน ALP
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-sky">฿</span>
                      <input id="view_alp" type="text" class="form-control text-end money-input"
                        value="{{ $sale->remainingPayment->total_alp !== null ? number_format($sale->remainingPayment->total_alp, 2) : '-' }}"
                        disabled>
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
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="view_interest" class="mf-label form-label">
                      <i class="bx bx-trending-up ci-indigo"></i> ดอกเบี้ย
                    </label>
                    <input id="view_interest" type="text" class="form-control" name="interest"
                      value="{{ $sale->remainingPayment->interest . '%' ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="view_tax" class="mf-label form-label">
                      <i class="bx bx-receipt ci-indigo"></i> ภาษีหัก ณ ที่จ่าย
                    </label>
                    <input id="view_tax" type="text" class="form-control" name="tax"
                      value="{{ $sale->remainingPayment?->financeInfo?->tax . '%' ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="view_type_com" class="mf-label form-label">
                      <i class="bx bx-category ci-indigo"></i> ประเภทคอม
                    </label>
                    <input id="view_type_com" type="text" class="form-control" name="type_com"
                      value="C{{ $sale->remainingPayment->type_com ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-6">
                    <label for="view_period" class="mf-label form-label">
                      <i class="bx bx-time ci-indigo"></i> จำนวนเดือนที่ผ่อน
                    </label>
                    @php $years = $sale->remainingPayment?->period ? $sale->remainingPayment->period / 12 : null; @endphp
                    <input id="view_period" type="text" class="form-control" name="period"
                      value="{{ $sale->remainingPayment->period ?? '-' }}" style="background:#f8fafc;color:#64748b;"
                      disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="date" class="mf-label form-label">
                      <i class="bx bx-calendar-check ci-indigo"></i> วันที่ได้รับเงิน
                    </label>
                    <input id="date" type="text" class="form-control" name="date"
                      value="{{ $fnCon->format_date ?? '-' }}" style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-6">
                    <label for="firm_date" class="mf-label form-label">
                      <i class="bx bx-calendar-event ci-indigo"></i> วันที่เฟิร์มเคส
                    </label>
                    <input id="firm_date" type="text" class="form-control" name="firm_date"
                      value="{{ $fnCon->firm_date ?? '-' }}" style="background:#f8fafc;color:#64748b;" disabled>
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
                    <td><input id="view_excellent" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="excellent"
                        value="{{ optional($fnCon)->excellent !== null ? number_format(optional($fnCon)->excellent, 2) : '' }}"
                        disabled></td>
                    <td><input id="excellent_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="excellent_accept"
                        value="{{ optional($fnCon)->excellent_accept !== null ? number_format(optional($fnCon)->excellent_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="excellent_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="excellent_diff"
                        value="{{ optional($fnCon)->excellent_diff !== null ? number_format(optional($fnCon)->excellent_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-center text-muted">2</td>
                    <td class="fw-semibold">Com Fin</td>
                    <td><input id="com_fin" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_fin"
                        value="{{ optional($fnCon)->com_fin !== null ? number_format(optional($fnCon)->com_fin, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_fin_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_fin_accept"
                        value="{{ optional($fnCon)->com_fin_accept !== null ? number_format(optional($fnCon)->com_fin_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_fin_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_fin_diff"
                        value="{{ optional($fnCon)->com_fin_diff !== null ? number_format(optional($fnCon)->com_fin_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-center text-muted">3</td>
                    <td class="fw-semibold">Com Extra</td>
                    <td><input id="com_extra" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_extra"
                        value="{{ optional($fnCon)->com_extra !== null ? number_format(optional($fnCon)->com_extra, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_extra_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="com_extra_accept"
                        value="{{ optional($fnCon)->com_extra_accept !== null ? number_format(optional($fnCon)->com_extra_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_extra_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_extra_diff"
                        value="{{ optional($fnCon)->com_extra_diff !== null ? number_format(optional($fnCon)->com_extra_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-center text-muted">4</td>
                    <td class="fw-semibold">Com Kickback</td>
                    <td><input id="view_kickback" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="kickback"
                        value="{{ $sale->kickback !== null ? number_format($sale->kickback, 2) : '-' }}" disabled>
                    </td>
                    <td><input id="com_kickback_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="com_kickback_accept"
                        value="{{ optional($fnCon)->com_kickback_accept !== null ? number_format(optional($fnCon)->com_kickback_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_kickback_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="com_kickback_diff"
                        value="{{ optional($fnCon)->com_kickback_diff !== null ? number_format(optional($fnCon)->com_kickback_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-center text-muted">5</td>
                    <td class="fw-semibold">Com Subsidy</td>
                    <td><input id="com_subsidy" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf" name="com_subsidy"
                        value="{{ optional($fnCon)->com_subsidy !== null ? number_format(optional($fnCon)->com_subsidy, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_subsidy_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="com_subsidy_accept"
                        value="{{ optional($fnCon)->com_subsidy_accept !== null ? number_format(optional($fnCon)->com_subsidy_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="com_subsidy_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="com_subsidy_diff"
                        value="{{ optional($fnCon)->com_subsidy_diff !== null ? number_format(optional($fnCon)->com_subsidy_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr class="border-bottom">
                    <td class="text-center text-muted">6</td>
                    <td class="fw-semibold">ค่างวดล่วงหน้า</td>
                    <td><input id="advance_installment" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="advance_installment"
                        value="{{ optional($fnCon)->advance_installment !== null ? number_format(optional($fnCon)->advance_installment, 2) : '' }}"
                        disabled></td>
                    <td><input id="advance_installment_accept" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="advance_installment_accept"
                        value="{{ optional($fnCon)->advance_installment_accept !== null ? number_format(optional($fnCon)->advance_installment_accept, 2) : '' }}"
                        disabled></td>
                    <td><input id="advance_installment_diff" type="text"
                        class="form-control form-control-sm text-end form-control-plaintext-mf"
                        name="advance_installment_diff"
                        value="{{ optional($fnCon)->advance_installment_diff !== null ? number_format(optional($fnCon)->advance_installment_diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                  <tr style="background:#fffbeb;border-top:2px solid #d97706;">
                    <td></td>
                    <td class="fw-bold">รวมเงินทั้งหมด</td>
                    <td><input id="total" type="text"
                        class="form-control form-control-sm text-end fw-semibold"
                        style="background:#fef9ec;border-color:#fde68a;" name="total"
                        value="{{ optional($fnCon)->total !== null ? number_format(optional($fnCon)->total, 2) : '' }}"
                        disabled></td>
                    <td><input id="actually_received" type="text"
                        class="form-control form-control-sm text-end fw-semibold"
                        style="background:#fef9ec;border-color:#fde68a;" name="actually_received"
                        value="{{ optional($fnCon)->actually_received !== null ? number_format(optional($fnCon)->actually_received, 2) : '' }}"
                        disabled></td>
                    <td><input id="diff" type="text"
                        class="form-control form-control-sm text-end fw-semibold"
                        style="background:#fef9ec;border-color:#fde68a;" name="diff"
                        value="{{ optional($fnCon)->diff !== null ? number_format(optional($fnCon)->diff, 2) : '' }}"
                        disabled></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
