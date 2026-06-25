<div class="modal fade editFilmPriceModel" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขราคาฟิล์ม</h6>
            <small class="text-white mf-hd-sub">{{ $model->Name_TH }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="formEditFilmPriceModel">
          @csrf
          <input type="hidden" id="ep_model_id" value="{{ $model->id }}">

          {{-- Section 1 : ข้อมูลรถ --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo"><i class="bx bx-car"></i></div>
              <span class="mf-section-title">ข้อมูลรถ — {{ $model->Name_TH }}</span>
              <div class="ms-auto d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" role="switch" id="ep_has_door_rear2"
                    name="has_door_rear2" value="1" {{ $hasDoorRear2 ? 'checked' : '' }}>
                  <label class="form-check-label mf-label" for="ep_has_door_rear2">ประตูหลัง 2</label>
                </div>
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" role="switch" id="ep_has_sunroof" name="has_sunroof"
                    value="1" {{ $hasSunroof ? 'checked' : '' }}>
                  <label class="form-check-label mf-label" for="ep_has_sunroof">
                    <i class="bx bx-sun ci-amber me-1"></i>ซันรูฟ
                  </label>
                </div>
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" role="switch" id="ep_has_3window" name="has_3window"
                    value="1" {{ $has3window ? 'checked' : '' }}>
                  <label class="form-check-label mf-label" for="ep_has_3window">แพ็กเกจ 3 บาน</label>
                </div>
              </div>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                {{-- ตร.ฟุต รวม --}}
                <div class="col-md-3">
                  <label for="ep_sqft" class="mf-label form-label">
                    <i class="bx bx-ruler ci-indigo"></i> ตร.ฟุต ทั้งหมด <span class="text-danger">*</span>
                  </label>
                  <input id="ep_sqft" type="number" name="sqft" class="form-control text-end" min="0"
                    step="0.01" placeholder="0.00" value="{{ $sqft }}">
                </div>

                {{-- ตร.ฟุตแต่ละตำแหน่ง --}}
                <div class="col-12">
                  <div class="mf-label form-label mb-2">
                    <i class="bx bx-grid-alt ci-indigo"></i> ตร.ฟุตแต่ละตำแหน่ง
                  </div>
                  <div class="row g-2">
                    <div class="col">
                      <label for="ep_sqft_around" class="form-label small text-muted mb-1">รอบคัน</label>
                      <input id="ep_sqft_around" type="number" name="sqft_around" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftAround }}">
                    </div>
                    <div class="col">
                      <label for="ep_sqft_windshield" class="form-label small text-muted mb-1">บานหน้า</label>
                      <input id="ep_sqft_windshield" type="number" name="sqft_windshield" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftWindshield }}">
                    </div>
                    <div class="col">
                      <label for="ep_sqft_rear" class="form-label small text-muted mb-1">บานหลัง</label>
                      <input id="ep_sqft_rear" type="number" name="sqft_rear" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftRear }}">
                    </div>
                    <div class="col">
                      <label for="ep_sqft_door_front" class="form-label small text-muted mb-1">ประตูคู่หน้า</label>
                      <input id="ep_sqft_door_front" type="number" name="sqft_door_front" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftDoorFront }}">
                    </div>
                    <div class="col">
                      <label for="ep_sqft_door_rear1" class="form-label small text-muted mb-1">ประตูคู่หลัง 1</label>
                      <input id="ep_sqft_door_rear1" type="number" name="sqft_door_rear1" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftDoorRear1 }}">
                    </div>
                    <div class="col">
                      <label for="ep_sqft_quarter" class="form-label small text-muted mb-1">หูช้าง</label>
                      <input id="ep_sqft_quarter" type="number" name="sqft_quarter" class="form-control form-control-sm text-end"
                        min="0" step="0.01" placeholder="0.00" value="{{ $sqftQuarter }}">
                    </div>
                  </div>
                </div>

                {{-- ตร.ฟุต ตามตัวเลือกเปิด --}}
                <div id="ep_door_rear2_fields" class="col-md-3 {{ $hasDoorRear2 ? '' : 'd-none' }}">
                  <label for="ep_sqft_door_rear2" class="form-label small text-muted mb-1">ตร.ฟุต ประตูคู่หลัง 2</label>
                  <input id="ep_sqft_door_rear2" type="number" name="sqft_door_rear2" class="form-control form-control-sm text-end"
                    min="0" step="0.01" placeholder="0.00" value="{{ $sqftDoorRear2 }}">
                </div>

                <div id="ep_sunroof_fields" class="col-md-3 {{ $hasSunroof ? '' : 'd-none' }}">
                  <label for="ep_sqft_sunroof" class="form-label small text-muted mb-1">
                    <i class="bx bx-sun ci-amber me-1"></i>ตร.ฟุต ซันรูฟ
                  </label>
                  <input id="ep_sqft_sunroof" type="number" name="sqft_sunroof"
                    class="form-control form-control-sm text-end" min="0" step="0.01" placeholder="0.00"
                    value="{{ $sqftSunroof }}">
                </div>

                <div id="ep_3window_fields" class="col-md-3 {{ $has3window ? '' : 'd-none' }}">
                  <label for="ep_sqft_3window" class="form-label small text-muted mb-1">ตร.ฟุต แพ็กเกจ 3 บาน</label>
                  <input id="ep_sqft_3window" type="number" name="sqft_3window" class="form-control form-control-sm text-end"
                    min="0" step="0.01" placeholder="0.00" value="{{ $sqft3window }}">
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ราคาตามยี่ห้อฟิล์ม --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-layer"></i>
              </div>
              <span class="mf-section-title">ราคาตามยี่ห้อฟิล์ม</span>
              <button type="button" class="btn btn-sm btn-outline-primary ms-auto btnAddEpBrandRow">
                <i class="bx bx-plus me-1"></i> เพิ่มยี่ห้อ
              </button>
            </div>
            <div class="mf-section-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered mb-0" id="epBrandTable">
                  <thead class="table-light">
                    <tr>
                      <th style="min-width:160px">ยี่ห้อฟิล์ม</th>
                      <th class="text-end" style="min-width:150px">ราคารวมภาษี (฿)</th>
                      <th class="text-end" style="min-width:130px">ค่าคอม SCom (฿)</th>
                      <th class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }} text-end" style="min-width:140px">
                        ราคาซันรูฟ (฿)</th>
                      <th class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }} text-end" style="min-width:130px">
                        ค่าคอมซันรูฟ (฿)</th>
                      <th class="col-3window {{ $has3window ? '' : 'd-none' }} text-end" style="min-width:140px">ราคา
                        3 บาน (฿)</th>
                      <th class="col-3window {{ $has3window ? '' : 'd-none' }} text-end" style="min-width:130px">
                        ค่าคอม 3 บาน (฿)</th>
                      <th style="width:50px"></th>
                    </tr>
                  </thead>
                  <tbody id="epBrandRows">

                    @forelse ($records as $i => $r)
                      <tr data-idx="{{ $i }}">
                        <td>
                          <select name="brands[{{ $i }}][film_brand_id]"
                            class="form-select form-select-sm epBrandSel">
                            <option value="">— เลือกยี่ห้อ —</option>
                            @foreach ($filmBrands as $fb)
                              <option value="{{ $fb->id }}"
                                {{ $r->film_brand_id == $fb->id ? 'selected' : '' }}>
                                {{ $fb->name }}
                              </option>
                            @endforeach
                          </select>
                        </td>
                        <td>
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-sky">฿</span>
                            <input type="text" name="brands[{{ $i }}][price]"
                              class="form-control text-end money-input"
                              value="{{ $r->price !== null ? number_format($r->price, 2) : '' }}" placeholder="0.00"
                              autocomplete="off">
                          </div>
                        </td>
                        <td>
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-sky">฿</span>
                            <input type="text" name="brands[{{ $i }}][commission]"
                              class="form-control text-end money-input"
                              value="{{ $r->commission !== null ? number_format($r->commission, 2) : '' }}"
                              placeholder="0.00" autocomplete="off">
                          </div>
                        </td>
                        <td class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }}">
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-amber">฿</span>
                            <input type="text" name="brands[{{ $i }}][price_sunroof]"
                              class="form-control text-end money-input"
                              value="{{ $r->price_sunroof !== null ? number_format($r->price_sunroof, 2) : '' }}"
                              placeholder="0.00" autocomplete="off">
                          </div>
                        </td>
                        <td class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }}">
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-amber">฿</span>
                            <input type="text" name="brands[{{ $i }}][commission_sunroof]"
                              class="form-control text-end money-input"
                              value="{{ $r->commission_sunroof !== null ? number_format($r->commission_sunroof, 2) : '' }}"
                              placeholder="0.00" autocomplete="off">
                          </div>
                        </td>
                        <td class="col-3window {{ $has3window ? '' : 'd-none' }}">
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-indigo">฿</span>
                            <input type="text" name="brands[{{ $i }}][price_3window]"
                              class="form-control text-end money-input"
                              value="{{ $r->price_3window !== null ? number_format($r->price_3window, 2) : '' }}"
                              placeholder="0.00" autocomplete="off">
                          </div>
                        </td>
                        <td class="col-3window {{ $has3window ? '' : 'd-none' }}">
                          <div class="input-group input-group-sm">
                            <span class="input-group-text ig-indigo">฿</span>
                            <input type="text" name="brands[{{ $i }}][commission_3window]"
                              class="form-control text-end money-input"
                              value="{{ $r->commission_3window !== null ? number_format($r->commission_3window, 2) : '' }}"
                              placeholder="0.00" autocomplete="off">
                          </div>
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger btnRemoveEpBrand">
                            <i class="bx bx-trash"></i>
                          </button>
                        </td>
                      </tr>
                    @empty
                      <tr id="epNoBrandMsg">
                        <td colspan="6" class="text-center text-muted py-3">
                          <i class="bx bx-info-circle me-1"></i> กดปุ่ม "เพิ่มยี่ห้อ" เพื่อเพิ่มข้อมูล
                        </td>
                      </tr>
                    @endforelse

                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateFilmPriceModel">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script>
  window.fpFilmBrands = @json($filmBrands->map(fn($fb) => ['id' => $fb->id, 'name' => $fb->name]));
  window.epNextIdx = {{ $records->count() }};
</script>
