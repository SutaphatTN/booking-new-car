<div class="modal fade viewWithdrawal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-receipt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ส่งเบิก / เคลียร์</h6>
            <small class="text-white mf-hd-sub">เลือกรายการที่ต้องการดำเนินการ</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Tabs --}}
        <ul class="nav nav-pills gap-2 mb-3">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-withdrawal">
              <i class="bx bx-upload me-1"></i> ส่งเบิก
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-clear">
              <i class="bx bx-check-circle me-1"></i> ส่งเคลียร์
            </button>
          </li>
        </ul>

        <div class="tab-content">

          {{-- ════ TAB ส่งเบิก ════ --}}
          <div class="tab-pane fade show active" id="tab-withdrawal">
            <div class="mf-section">
              <div class="mf-section-hd">
                <div class="mf-section-icon emerald">
                  <i class="bx bx-upload"></i>
                </div>
                <span class="mf-section-title">รายการรอส่งเบิก</span>
              </div>
              <div class="mf-section-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0" style="min-width:900px;">
                    <thead class="table-success">
                      <tr>
                        <th class="text-center" style="width:42px;"><input type="checkbox" id="checkAll"></th>
                        <th>ชื่อลูกค้า</th>
                        <th style="width:145px;min-width:145px;">VIN</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ตรวจ</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ช่อง</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ใบเสร็จ</th>
                        <th class="text-center" style="width:120px;min-width:120px;">รวมเบิก</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($withdrawalData as $item)
                        <tr>
                          <td class="text-center">
                            <input type="checkbox" class="checkItem" value="{{ $item->id }}">
                          </td>
                          <td class="fw-semibold" style="font-size:.82rem;">
                            {{ $item->customer?->prefix?->Name_TH }}
                            {{ $item->customer?->FirstName }}
                            {{ $item->customer?->LastName }}
                          </td>
                          <td class="font-monospace text-muted" style="font-size:.78rem;">
                            {{ $item->carOrder?->vin_number }}
                          </td>
                          <td>
                            <input type="text" name="withdrawal[{{ $item->id }}][check]"
                              class="form-control calc-input withdrawal-check money-input text-end"
                              data-id="{{ $item->id }}" value="{{ number_format(600, 2) }}">
                          </td>
                          <td>
                            <input type="text" name="withdrawal[{{ $item->id }}][channel]"
                              class="form-control calc-input withdrawal-channel money-input text-end"
                              data-id="{{ $item->id }}" value="{{ number_format(200, 2) }}">
                          </td>
                          <td>
                            <input type="text" name="withdrawal[{{ $item->id }}][receipt]"
                              class="form-control calc-input withdrawal-bill money-input text-end"
                              data-id="{{ $item->id }}">
                          </td>
                          <td>
                            <input type="text" name="withdrawal[{{ $item->id }}][total]"
                              class="form-control withdrawal-total text-end fw-semibold form-control-plaintext-mf"
                              data-id="{{ $item->id }}" readonly>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
              <button class="btn btn-success px-5 btnConfirmWithdrawal">
                <i class="bx bx-upload me-1"></i> ยืนยันส่งเบิก
              </button>
            </div>
          </div>

          {{-- ════ TAB ส่งเคลียร์ ════ --}}
          <div class="tab-pane fade" id="tab-clear">
            <div class="mf-section">
              <div class="mf-section-hd">
                <div class="mf-section-icon sky">
                  <i class="bx bx-check-circle"></i>
                </div>
                <span class="mf-section-title">รายการรอส่งเคลียร์</span>
              </div>
              <div class="mf-section-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm table-hover align-middle mb-0" style="min-width:900px;">
                    <thead class="table-info">
                      <tr>
                        <th class="text-center" style="width:42px;"><input type="checkbox" id="checkAllClear"></th>
                        <th>ชื่อลูกค้า</th>
                        <th style="width:145px;min-width:145px;">VIN</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ตรวจ</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ช่อง</th>
                        <th class="text-center" style="width:120px;min-width:120px;">ใบเสร็จ</th>
                        <th class="text-center" style="width:120px;min-width:120px;">รวมเคลียร์</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($clearData as $item)
                        <tr>
                          <td class="text-center">
                            <input type="checkbox" class="checkItemClear" value="{{ $item->id }}">
                          </td>
                          <td class="fw-semibold" style="font-size:.82rem;">
                            {{ $item->customer?->prefix?->Name_TH }}
                            {{ $item->customer?->FirstName }}
                            {{ $item->customer?->LastName }}
                          </td>
                          <td class="font-monospace text-muted" style="font-size:.78rem;">
                            {{ $item->carOrder?->vin_number }}
                          </td>
                          <td>
                            <input type="text" name="clear[{{ $item->id }}][check]"
                              class="form-control calc-clear receipt-check money-input text-end"
                              data-id="{{ $item->id }}" value="{{ number_format(600, 2) }}">
                          </td>
                          <td>
                            <input type="text" name="clear[{{ $item->id }}][channel]"
                              class="form-control calc-clear receipt-channel money-input text-end"
                              data-id="{{ $item->id }}" value="{{ number_format(200, 2) }}">
                          </td>
                          <td>
                            <input type="text" name="clear[{{ $item->id }}][bill]"
                              class="form-control calc-clear receipt-bill money-input text-end"
                              data-id="{{ $item->id }}">
                          </td>
                          <td>
                            <input type="text" name="clear[{{ $item->id }}][total]"
                              class="form-control receipt-total text-end fw-semibold form-control-plaintext-mf"
                              data-id="{{ $item->id }}" readonly>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
              <button class="btn btn-primary px-5 btnConfirmClear">
                <i class="bx bx-check-circle me-1"></i> ยืนยันส่งเคลียร์
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
