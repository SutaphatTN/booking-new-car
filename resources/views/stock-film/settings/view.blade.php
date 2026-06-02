@extends('layouts/contentNavbarLayout')
@section('title', 'ตั้งค่าฟิล์ม')

@section('page-script')
@vite(['resources/assets/js/film-settings.js'])
@endsection

@section('content')
<div class="row g-4">

  {{-- Global Settings --}}
  <div class="col-12">
    <div class="card tbl-card">
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-cog fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">Global Settings</div>
          <div class="text-white mf-hd-sub">ตั้งค่าทั่วไป</div>
        </div>
      </div>
      <div class="card-body">
        <form id="formGlobal">
          @csrf
          <div class="row g-3 align-items-end">

            <div class="col-md-3">
              <label for="gs_roll_size" class="mf-label form-label">
                <i class="bx bx-ruler"></i> ขนาดม้วน (ตร.ฟุต)
              </label>
              <input id="gs_roll_size" type="text" name="roll_size" class="form-control text-end money-input-dec"
                value="{{ number_format($global->roll_size, 2) }}" autocomplete="off" required>
            </div>

            <div class="col-md-3">
              <label for="gs_waste_pct" class="mf-label form-label">
                <i class="bx bx-transfer"></i> Waste %
              </label>
              <div class="input-group">
                <input id="gs_waste_pct" type="number" name="waste_pct" class="form-control text-end"
                  value="{{ $global->waste_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>

            <div class="col-md-3">
              <label for="gs_gp_pct" class="mf-label form-label">
                <i class="bx bx-trending-up"></i> GP%
              </label>
              <div class="input-group">
                <input id="gs_gp_pct" type="number" name="gp_pct" class="form-control text-end"
                  value="{{ $global->gp_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>

            <div class="col-md-3">
              <label for="gs_commission_pct" class="mf-label form-label">
                <i class="bx bx-badge-check"></i> Commission %
              </label>
              <div class="input-group">
                <input id="gs_commission_pct" type="number" name="commission_pct" class="form-control text-end"
                  value="{{ $global->commission_pct }}" min="0" max="100" step="0.01" required>
                <span class="input-group-text">%</span>
              </div>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-primary px-5 btnSaveGlobal">
                <i class="bx bx-save me-1"></i> บันทึก Global Settings
              </button>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Film Cost Settings --}}
  <div class="col-12">
    <div class="card tbl-card">
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-layer fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ต้นทุนฟิล์ม</div>
          <div class="text-white mf-hd-sub">Film Cost Settings</div>
        </div>
      </div>
      <div class="card-body">
        <form id="formCosts">
          @csrf
          <div class="table-responsive">
            <table class="table table-bordered tbl-table">
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
                    $cs = $costSettings[$fb->id] ?? null;
                    $rollPrice = $cs ? $cs->roll_price : 0;
                    $discount  = $cs ? $cs->discount : 0;
                  @endphp
                  <tr>
                    <td class="align-middle fw-bold">{{ $fb->name }}</td>
                    <td>
                      <input type="text" name="costs[{{ $fb->id }}][roll_price]"
                        id="cost_roll_{{ $fb->id }}"
                        class="form-control text-end money-input-dec cost-roll"
                        data-id="{{ $fb->id }}"
                        value="{{ number_format($rollPrice, 2) }}"
                        autocomplete="off">
                    </td>
                    <td>
                      {{-- display: positive (user input) --}}
                      <input type="text"
                        id="cost_discount_display_{{ $fb->id }}"
                        class="form-control text-end money-input-dec cost-discount-display"
                        data-id="{{ $fb->id }}"
                        value="{{ number_format(abs($discount), 2) }}"
                        autocomplete="off">
                      {{-- hidden: stored as negative --}}
                      <input type="hidden" name="costs[{{ $fb->id }}][discount]"
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
                        @else
                          -
                        @endif
                      </span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-primary px-5 btnSaveCosts">
              <i class="bx bx-save me-1"></i> บันทึกต้นทุนฟิล์ม
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>
@endsection
