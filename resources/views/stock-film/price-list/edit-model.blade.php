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
              <div class="mf-section-icon indigo">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถ — {{ $model->Name_TH }}</span>
              <div class="ms-auto form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                  id="ep_has_sunroof" name="has_sunroof" value="1"
                  {{ $hasSunroof ? 'checked' : '' }}>
                <label class="form-check-label mf-label" for="ep_has_sunroof">
                  <i class="bx bx-sun ci-amber me-1"></i>มีซันรูฟ
                </label>
              </div>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="ep_sqft" class="mf-label form-label">
                    <i class="bx bx-ruler ci-sky"></i> จำนวน ตร.ฟุต (รอบคัน+บานหน้า) <span class="text-danger">*</span>
                  </label>
                  <input id="ep_sqft" type="number" name="sqft" class="form-control text-end"
                    min="0" step="0.01" placeholder="0.00" value="{{ $sqft }}">
                </div>

                <div id="ep_sunroof_fields" class="col-md-4 {{ $hasSunroof ? '' : 'd-none' }}">
                  <label for="ep_sqft_sunroof" class="mf-label form-label">
                    <i class="bx bx-ruler ci-amber"></i> จำนวน ตร.ฟุต ซันรูฟ
                  </label>
                  <input id="ep_sqft_sunroof" type="number" name="sqft_sunroof" class="form-control text-end"
                    min="0" step="0.01" placeholder="0.00" value="{{ $sqftSunroof }}">
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
                      <th style="min-width:180px">ยี่ห้อฟิล์ม</th>
                      <th class="text-end" style="min-width:160px">ราคาขายรวมภาษี (฿)</th>
                      <th class="text-end" style="min-width:140px">ค่าคอม SCom (฿)</th>
                      <th class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }} text-end" style="min-width:150px">ราคาซันรูฟ (฿)</th>
                      <th class="col-sunroof {{ $hasSunroof ? '' : 'd-none' }} text-end" style="min-width:140px">ค่าคอมซันรูฟ (฿)</th>
                      <th style="width:50px"></th>
                    </tr>
                  </thead>
                  <tbody id="epBrandRows">

                    @forelse ($records as $i => $r)
                    <tr data-idx="{{ $i }}">
                      <td>
                        <select name="brands[{{ $i }}][film_brand_id]" class="form-select form-select-sm epBrandSel">
                          <option value="">— เลือกยี่ห้อ —</option>
                          @foreach ($filmBrands as $fb)
                            <option value="{{ $fb->id }}" {{ $r->film_brand_id == $fb->id ? 'selected' : '' }}>
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
                            value="{{ $r->price !== null ? number_format($r->price, 2) : '' }}"
                            placeholder="0.00" autocomplete="off">
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
  window.epNextIdx    = {{ $records->count() }};
</script>
