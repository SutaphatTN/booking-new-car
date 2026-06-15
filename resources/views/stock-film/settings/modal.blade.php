<div class="modal fade filmSettings" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bx bx-cog text-secondary"></i> ตั้งค่าฟิล์ม
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        {{-- Global Settings --}}
        <p class="fw-bold text-secondary small mb-2">
          <i class="bx bx-slider-alt me-1"></i> Global Settings
        </p>
        <form id="formGlobal">
          @csrf
          <div class="row g-3 align-items-end mb-1">
            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">ขนาดม้วน (ตร.ฟุต)</label>
              <input id="gs_roll_size" type="text" name="roll_size"
                class="form-control text-end money-input-dec"
                value="{{ number_format($global->roll_size, 2) }}"
                autocomplete="off" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">Waste %</label>
              <div class="input-group">
                <input id="gs_waste_pct" type="number" name="waste_pct"
                  class="form-control text-end"
                  value="{{ $global->waste_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">GP%</label>
              <div class="input-group">
                <input id="gs_gp_pct" type="number" name="gp_pct"
                  class="form-control text-end"
                  value="{{ $global->gp_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label small text-muted mb-1">Commission %</label>
              <div class="input-group">
                <input id="gs_commission_pct" type="number" name="commission_pct"
                  class="form-control text-end"
                  value="{{ $global->commission_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>
          </div>
        </form>

        <hr class="my-3">

        {{-- Film Cost Settings --}}
        <p class="fw-bold text-secondary small mb-2">
          <i class="bx bx-layer me-1"></i> ต้นทุนฟิล์ม
        </p>
        <form id="formCosts">
          @csrf
          <div class="table-responsive">
            <table class="table table-bordered tbl-table mb-1">
              <thead>
                <tr>
                  <th>ยี่ห้อฟิล์ม</th>
                  <th class="text-end">ราคาต่อม้วน (฿)</th>
                  <th class="text-end">ส่วนลด (฿)</th>
                  <th class="text-end">ราคาสุทธิ (฿)</th>
                  <th class="text-end">ต้นทุน/ตร.ฟุต (฿)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($filmBrands as $fb)
                  @php
                    $cs        = $costSettings[$fb->id] ?? null;
                    $rollPrice = $cs ? $cs->roll_price : 0;
                    $discount  = $cs ? $cs->discount  : 0;
                  @endphp
                  <tr>
                    <td class="align-middle fw-bold">{{ $fb->name }}</td>
                    <td>
                      <input type="text"
                        name="costs[{{ $fb->id }}][roll_price]"
                        id="cost_roll_{{ $fb->id }}"
                        class="form-control form-control-sm text-end money-input-dec cost-roll"
                        data-id="{{ $fb->id }}"
                        value="{{ number_format($rollPrice, 2) }}"
                        autocomplete="off">
                    </td>
                    <td>
                      <input type="text"
                        id="cost_discount_display_{{ $fb->id }}"
                        class="form-control form-control-sm text-end money-input-dec cost-discount-display"
                        data-id="{{ $fb->id }}"
                        value="{{ number_format(abs($discount), 2) }}"
                        autocomplete="off">
                      <input type="hidden"
                        name="costs[{{ $fb->id }}][discount]"
                        id="cost_discount_{{ $fb->id }}"
                        class="cost-discount"
                        data-id="{{ $fb->id }}"
                        value="{{ $discount }}">
                    </td>
                    <td class="align-middle text-end">
                      <span id="final_cost_{{ $fb->id }}" class="fw-bold text-primary">
                        {{ number_format($rollPrice + $discount, 2) }}
                      </span>
                    </td>
                    <td class="align-middle text-end">
                      <span id="per_sqft_{{ $fb->id }}" class="fw-bold text-success">
                        @if($global->roll_size > 0)
                          {{ number_format(($rollPrice + $discount) / $global->roll_size, 2) }}
                        @else -
                        @endif
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </form>

      </div>{{-- /modal-body --}}

      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i> ปิด
        </button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-primary btn-sm btnSaveGlobal">
            <i class="bx bx-save me-1"></i> บันทึก Global
          </button>
          <button type="button" class="btn btn-primary btn-sm btnSaveCosts">
            <i class="bx bx-save me-1"></i> บันทึกต้นทุน
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
