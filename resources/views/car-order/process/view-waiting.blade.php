<div class="modal fade viewWaitingOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายละเอียดคำขอสั่งรถ (Waiting)</h6>
            <small class="text-white mf-hd-sub">{{ $waiting->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section 1 : ข้อมูลการสั่งซื้อ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-list-ul"></i>
            </div>
            <span class="mf-section-title">ข้อมูลการสั่งซื้อ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-3">
                <label for="type" class="mf-label form-label">
                  <i class="bx bx-category ci-indigo"></i> ประเภทการสั่งรถ
                </label>
                <input id="type" type="text" class="form-control" value="{{ $waiting->type }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="count_order" class="mf-label form-label">
                  <i class="bx bx-hash ci-indigo"></i> จำนวนที่สั่ง (คัน)
                </label>
                <input id="count_order" type="text" class="form-control" value="{{ $waiting->count_order }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="purchase_source" class="mf-label form-label">
                  <i class="bx bx-store ci-indigo"></i> แหล่งที่มา
                </label>
                <input id="purchase_source" type="text" class="form-control" value="{{ $waiting->purchase_source }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="purchase_type" class="mf-label form-label">
                  <i class="bx bx-transfer ci-indigo"></i> ประเภทการซื้อรถ
                </label>
                <input id="purchase_type" type="text" class="form-control"
                  value="{{ $waiting->purchaseType->name ?? '-' }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 2 : ข้อมูลรุ่นรถ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky">
              <i class="bx bx-car"></i>
            </div>
            <span class="mf-section-title">ข้อมูลรุ่นรถ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              <div class="col-md-5">
                <label for="model_id" class="mf-label form-label">
                  <i class="bx bx-car"></i> รุ่นรถหลัก
                </label>
                <input id="model_id" type="text" class="form-control" value="{{ $waiting->model->Name_TH ?? '-' }}"
                  disabled>
              </div>

              <div class="col-md-7">
                <label for="subModel_id" class="mf-label form-label">
                  <i class="bx bx-barcode"></i> รุ่นรถย่อย
                </label>
                <input id="subModel_id" type="text" class="form-control"
                  value="{{ $waiting->subModel ? ($waiting->subModel->detail ? $waiting->subModel->detail . ' - ' : '') . $waiting->subModel->name : '-' }}"
                  disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 3 : รายละเอียดรถและราคา --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon amber">
              <i class="bx bx-money"></i>
            </div>
            <span class="mf-section-title">รายละเอียดรถและราคา</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">

              @if (auth()->user()->brand == 2)
                <div class="col-md-4">
                  <label for="gwm_color" class="mf-label form-label">
                    <i class="bx bx-palette ci-amber"></i> สี
                  </label>
                  <input id="gwm_color" type="text" class="form-control"
                    value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
                </div>

                <div class="col-md-4">
                  <label for="interior_color" class="mf-label form-label">
                    <i class="bx bx-color-fill ci-amber"></i> สีภายใน
                  </label>
                  <input id="interior_color" type="text" class="form-control"
                    value="{{ $waiting->interiorColor->name ?? '-' }}" disabled>
                </div>

                <div class="col-md-4">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> ปี
                  </label>
                  <input id="year" type="text" class="form-control" value="{{ $waiting->year ?? '-' }}"
                    disabled>
                </div>

                <div class="col-md-4">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control"
                    value="{{ $waiting->approvers->name ?? '-' }}" disabled>
                </div>
              @elseif (in_array(auth()->user()->brand, [3, 4]))
                <div class="col-md-4">
                  <label for="gwm_color" class="mf-label form-label">
                    <i class="bx bx-palette ci-amber"></i> สี
                  </label>
                  <input id="gwm_color" type="text" class="form-control"
                    value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
                </div>

                <div class="col-md-4">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> ปี
                  </label>
                  <input id="year" type="text" class="form-control" value="{{ $waiting->year ?? '-' }}"
                    disabled>
                </div>

                <div class="col-md-4">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control"
                    value="{{ $waiting->approvers->name ?? '-' }}" disabled>
                </div>
              @else
                <div class="col-md-2">
                  <label for="option" class="mf-label form-label">
                    <i class="bx bx-list-check ci-amber"></i> Option
                  </label>
                  <input id="option" type="text" class="form-control" value="{{ $waiting->option ?? '-' }}"
                    disabled>
                </div>

                <div class="col-md-3">
                  <label for="color" class="mf-label form-label">
                    <i class="bx bx-color-fill ci-amber"></i> สี
                  </label>
                  <input id="color" type="text" class="form-control" value="{{ $waiting->color ?? '-' }}"
                    disabled>
                </div>

                <div class="col-md-3">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> ปี
                  </label>
                  <input id="year" type="text" class="form-control" value="{{ $waiting->year ?? '-' }}"
                    disabled>
                </div>

                <div class="col-md-4">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" type="text" class="form-control text-end"
                      value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="RI" class="mf-label form-label">
                    <i class="bx bx-coin-stack ci-amber"></i> RI
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="RI" type="text" class="form-control text-end"
                      value="{{ $waiting->RI !== null ? number_format($waiting->RI, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="WS" class="mf-label form-label">
                    <i class="bx bx-coin-stack ci-amber"></i> WS
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="WS" type="text" class="form-control text-end"
                      value="{{ $waiting->WS !== null ? number_format($waiting->WS, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-5">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control"
                    value="{{ $waiting->approvers->name ?? '-' }}" disabled>
                </div>
              @endif

            </div>
          </div>
        </div>

        {{-- สรุปสต็อกคงเหลือ : แจกแจงตามรุ่นย่อย → สี (ไม่รวมไม่อนุมัติ และส่งมอบแล้ว) --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon emerald">
              <i class="bx bx-bar-chart-alt-2"></i>
            </div>
            <span class="mf-section-title">
              สต็อกคงเหลือรุ่นนี้
              <span class="badge bg-label-primary ms-2">รวม {{ $colorTotal ?? 0 }} คัน</span>
            </span>
          </div>
          <div class="mf-section-body">
            <div class="text-muted mb-3" style="font-size:.8rem;">
              <i class="bx bx-info-circle"></i> ไม่รวมรายการที่ไม่อนุมัติ และที่ส่งมอบแล้ว (Delivered)
            </div>
            @if (!empty($stockSummary) && $stockSummary->count() > 0)
              <div class="d-flex flex-column gap-3">
                @foreach ($stockSummary as $subName => $info)
                  <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;">
                    {{-- หัวการ์ดรุ่นย่อย --}}
                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                      style="background:#f8fafc;border-bottom:1px solid #eef2f7;">
                      <span class="fw-semibold d-flex align-items-center gap-2" style="color:#334155;font-size:.9rem;">
                        <i class="bx bx-barcode" style="color:#0ea5e9;"></i> {{ $subName }}
                      </span>
                      <span class="d-inline-flex align-items-center gap-1 fw-bold"
                        style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;border-radius:999px;padding:2px 12px;font-size:.82rem;">
                        <i class="bx bx-car"></i> {{ $info['count'] }} คัน
                      </span>
                    </div>

                    {{-- ตารางปี/สี --}}
                    <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                      <thead>
                        <tr style="background:#fff;color:#94a3b8;font-size:.78rem;">
                          <th style="width:70px;" class="text-center fw-semibold border-0">ปี</th>
                          <th class="fw-semibold border-0">สี</th>
                          <th style="width:120px;" class="text-center fw-semibold border-0">คงเหลือ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($info['items'] as $item)
                          <tr style="border-top:1px solid #f1f5f9;">
                            <td class="text-center">
                              <span style="background:#eff6ff;color:#2563eb;border-radius:6px;padding:1px 8px;font-size:.8rem;">
                                {{ $item['year'] }}
                              </span>
                            </td>
                            <td style="color:#334155;">{{ $item['color'] }}</td>
                            <td class="text-center">
                              <span class="fw-bold" style="color:#0f172a;">{{ $item['count'] }}</span>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center text-muted py-4" style="font-size:.9rem;">
                <i class="bx bx-package d-block mb-2" style="font-size:2rem;opacity:.35;"></i>
                ไม่มีสต็อกคงเหลือของรุ่นนี้
              </div>
            @endif
          </div>
        </div>

        {{-- Section 4 : หมายเหตุ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon rose">
              <i class="bx bx-note"></i>
            </div>
            <span class="mf-section-title">หมายเหตุ</span>
          </div>
          <div class="mf-section-body">
            <textarea id="note" class="form-control" rows="2" disabled>{{ $waiting->note ?? '-' }}</textarea>
          </div>
        </div>

        {{-- Actions --}}
        {{-- <div class="d-flex justify-content-end pt-1">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ปิด
          </button>
        </div> --}}

      </div>

    </div>
  </div>
</div>
