@extends('layouts/contentNavbarLayout')
@section('title', 'SSI หลังส่งมอบ')

@section('page-script')
  <script>
    $(document).ready(function() {

      const salecarId = {{ $info['salecar_id'] }};

      // ── Score buttons ──
      $(document).on('click', '.score-btn', function() {
        const $group = $(this).closest('.score-group');
        const val = parseInt($(this).data('val'));
        $group.find('.score-btn').removeClass('selected score-low score-mid score-high');
        $(this).addClass('selected');
        if (val <= 4) $(this).addClass('score-low');
        else if (val <= 7) $(this).addClass('score-mid');
        else $(this).addClass('score-high');
        $group.find('input[type=hidden]').val(val);
      });

      // Restore saved scores on load
      $('.score-group').each(function() {
        const saved = parseInt($(this).find('input[type=hidden]').val());
        if (saved >= 1 && saved <= 10) {
          $(this).find(`.score-btn[data-val="${saved}"]`).trigger('click');
        }
      });

      // ── Amount formatting helpers ──
      function parseAmount(str) {
        return parseFloat(String(str).replace(/,/g, '')) || 0;
      }

      function formatAmount(val) {
        const n = parseFloat(String(val).replace(/,/g, ''));
        if (isNaN(n)) return '';
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      // Format on keyup: allow digits, one dot, strip the rest
      $('.amount-fmt').on('input', function () {
        const $el    = $(this);
        const raw    = $el.val().replace(/,/g, '');
        const clean  = raw.replace(/[^0-9.]/g, '');
        const parts  = clean.split('.');
        const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const decPart = parts.length > 1 ? '.' + parts[1].slice(0, 2) : '';
        $el.val(intPart + decPart);
        calcDiff();
      });

      // On blur: pad to 2 decimal places
      $('.amount-fmt').on('blur', function () {
        const n = parseAmount($(this).val());
        if (n) $(this).val(formatAmount(n));
        calcDiff();
      });

      // ── Diff auto-calc ──
      function calcDiff() {
        const a = parseAmount($('#amount_admin').val());
        const c = parseAmount($('#amount_customer').val());
        const d = a - c;
        if (a === 0 && c === 0) {
          $('#diff_display').val('').removeClass('text-success text-danger text-secondary');
          return;
        }
        $('#diff_display')
          .val(d.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))
          .removeClass('text-success text-danger text-secondary')
          .addClass(d > 0 ? 'text-danger' : d < 0 ? 'text-success' : 'text-secondary');
      }

      // Format initial values on load
      $('.amount-fmt').each(function () {
        const n = parseAmount($(this).val());
        if (n) $(this).val(formatAmount(n));
      });
      calcDiff();

      // ── Toggle transfer correct ──
      function toggleTransfer() {
        $('input[name="payment_channel"]:checked').val() === 'transfer' ?
          $('#row_transfer_correct').slideDown(150) :
          $('#row_transfer_correct').slideUp(150);
      }
      $('input[name="payment_channel"]').on('change', toggleTransfer);
      toggleTransfer();

      // ── Build contact card HTML ──
      function buildContactCard(c) {
        const contactedBadge = c.contacted ?
          '<span class="badge bg-label-success"><i class="bx bx-check me-1"></i>ติดต่อได้</span>' :
          '<span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>ติดต่อไม่ได้</span>';

        let interviewBadge = '';
        if (c.contacted && c.interview_success !== null && c.interview_success !== undefined) {
          interviewBadge = c.interview_success ?
            '<span class="badge bg-label-success ms-1"><i class="bx bx-check me-1"></i>สัมภาษณ์เรียบร้อย</span>' :
            '<span class="badge bg-label-warning text-warning ms-1"><i class="bx bx-error me-1"></i>สัมภาษณ์ไม่เรียบร้อย</span>';
        }

        return `
      <div class="contact-card" data-id="${c.id}">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="contact-card-no"><i class="bx bx-phone-call me-1"></i>ติดต่อครั้งที่ ${c.no}</div>
          <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-contact"
            data-id="${c.id}" title="ลบ" style="width:28px;height:28px;padding:0;">
            <i class="bx bx-trash" style="font-size:.85rem;"></i>
          </button>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
          <span class="text-muted" style="font-size:.82rem;"><i class="bx bx-calendar me-1"></i>${c.contact_date}</span>
          ${contactedBadge}${interviewBadge}
        </div>
        ${c.remark ? `<div class="mt-1" style="font-size:.83rem;color:#475569;"><i class="bx bx-comment-detail me-1 text-muted"></i>${c.remark}</div>` : ''}
      </div>`;
      }

      // Render contacts on load
      @foreach ($ssiRecord->contacts as $i => $contact)
        $('#contactList').append(buildContactCard({
          id: {{ $contact->id }},
          no: {{ $i + 1 }},
          contact_date: '{{ $contact->contact_date ? \Carbon\Carbon::parse($contact->contact_date)->format('d/m/Y') : '-' }}',
          contacted: {{ $contact->contacted ? 'true' : 'false' }},
          interview_success: {{ $contact->interview_success === null ? 'null' : ($contact->interview_success ? 'true' : 'false') }},
          remark: `{{ addslashes($contact->remark ?? '') }}`,
        }));
      @endforeach

      @if ($ssiRecord->contacts->count() > 0)
        $('#noContactMsg').hide();
      @endif

      // ── Modal: Add contact ──
      $('#btnAddContact').on('click', function() {
        resetContactModal();
        $('#modalAddContact').modal('show');
      });

      function resetContactModal() {
        $('#contact_date').val('');
        $('input[name="cnt_radio"]').prop('checked', false);
        $('input[name="ssi_int_radio"]').prop('checked', false);
        $('#contact_remark').val('');
        $('#row_interview').slideUp(0);
      }

      $('input[name="cnt_radio"]').on('change', function() {
        $(this).val() === '1' ? $('#row_interview').slideDown(150) : $('#row_interview').slideUp(150);
        $('input[name="ssi_int_radio"]').prop('checked', false);
      });

      $('#btnSaveContact').on('click', function() {
        const date = $('#contact_date').val();
        const contacted = $('input[name="cnt_radio"]:checked').val();
        if (!date) {
          Swal.fire({
            icon: 'warning',
            title: 'กรุณาระบุวันที่ติดต่อ',
            timer: 2000,
            showConfirmButton: true
          });
          return;
        }
        if (contacted === undefined) {
          Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกสถานะการติดต่อ',
            timer: 2000,
            showConfirmButton: true
          });
          return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i>');

        $.ajax({
          url: `/ssi/${salecarId}/contact`,
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            contact_date: date,
            contacted: contacted,
            interview_success: contacted === '1' ? ($('input[name="ssi_int_radio"]:checked').val() ?? null) :
              null,
            remark: $('#contact_remark').val(),
          },
          success: function(res) {
            $('#noContactMsg').hide();
            $('#contactList').append(buildContactCard(res.contact));
            $('#modalAddContact').modal('hide');
            Swal.fire({
              icon: 'success',
              title: 'เพิ่มการติดต่อสำเร็จ',
              timer: 1200,
              showConfirmButton: true
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'ผิดพลาด',
              text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด'
            });
          },
          complete: function() {
            $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
          }
        });
      });

      // ── Delete contact ──
      $(document).on('click', '.btn-delete-contact', function() {
        const contactId = $(this).data('id');
        const $card = $(this).closest('.contact-card');
        Swal.fire({
          title: 'คุณแน่ใจหรือไม่?',
          text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#6c5ffc',
          cancelButtonColor: '#d33',
          confirmButtonText: 'ใช่, ลบเลย!',
          cancelButtonText: 'ยกเลิก'
        }).then(function(r) {
          if (!r.isConfirmed) return;
          $.ajax({
            url: `/ssi/${salecarId}/contact/${contactId}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
              $card.fadeOut(200, function() {
                $(this).remove();
                $('#contactList .contact-card').each(function(i) {
                  $(this).find('.contact-card-no').html(
                    `<i class="bx bx-phone-call me-1"></i>ติดต่อครั้งที่ ${i + 1}`);
                });
                if ($('#contactList .contact-card').length === 0) $('#noContactMsg').show();
              });
            },
            error: function() {
              Swal.fire({
                icon: 'error',
                title: 'ลบไม่สำเร็จ'
              });
            }
          });
        });
      });

      // ── Save Tab 2 ──
      $('#btnSaveTab2').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> กำลังบันทึก...');

        $.ajax({
          url: `/ssi/${salecarId}/tab2`,
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            dw_website: $('#score_dw_website').val() || null,
            q11_facilities: $('#score_q11_facilities').val() || null,
            q15_car_knowledge: $('#score_q15_car_knowledge').val() || null,
            q17_service_responsibility: $('#score_q17_service_responsibility').val() || null,
            q18_sales_conditions: $('#score_q18_sales_conditions').val() || null,
            o27_car_condition: $('#score_o27_car_condition').val() || null,
            fu_followup: $('#score_fu_followup').val() || null,
            recommend_showroom: $('#score_recommend_showroom').val() || null,
            sop14_test_drive: $('#score_sop14_test_drive').val() || null,
            sop24_update_progress: $('#score_sop24_update_progress').val() || null,
            sop25_accessories_complete: $('#score_sop25_accessories_complete').val() || null,
            sop30_satisfaction_followup: $('#score_sop30_satisfaction_followup').val() || null,
            amount_admin: parseAmount($('#amount_admin').val()) || null,
            amount_customer: parseAmount($('#amount_customer').val()) || null,
            payment_channel: $('input[name="payment_channel"]:checked').val() || null,
            transfer_correct: $('input[name="transfer_correct"]:checked').val() ?? null,
            payment_remark: $('#payment_remark').val(),
            compliment: $('#compliment').val(),
            suggestion: $('#suggestion').val(),
            complaint: $('#complaint').val(),
            cro_comment: $('#cro_comment').val(),
            sm_resolution: $('#sm_resolution').val(),
            resolution_date: $('#resolution_date').val(),
            resolution_status: $('#resolution_status').val(),
            correction_form_sent_date: $('#correction_form_sent_date').val(),
          },
          success: function(res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ',
              text: res.message,
              timer: 1500,
              showConfirmButton: true
            });
          },
          error: function(xhr) {
            Swal.fire({
              icon: 'error',
              title: 'ผิดพลาด',
              text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด'
            });
          },
          complete: function() {
            $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึกข้อมูล');
          }
        });
      });

    });
  </script>
@endsection

@section('content')

  {{-- Page Header --}}
  <div class="pur-page-title justify-content-between flex-wrap gap-2 mb-3">
    <div class="d-flex align-items-center gap-3">
      <div class="pur-page-icon" style="background: linear-gradient(135deg, #0284c7, #38bdf8);">
        <i class="bx bx-star"></i>
      </div>
      <div>
        <h5 class="pur-page-name mb-0">SSI หลังส่งมอบ</h5>
        <small class="text-muted" style="font-size:.8rem;">{{ $info['full_name'] }}</small>
      </div>
    </div>
    <a href="{{ route('ssi.index') }}" class="btn btn-outline-danger btn-sm">
      <i class="bx bx-arrow-back me-1"></i> ย้อนกลับ
    </a>
  </div>

  {{-- Tabs --}}
  <div class="nav-align-top">
    <ul class="nav nav-pills mb-4 nav-fill" id="ssiTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-contact" type="button">
          <i class="bx bx-phone me-1"></i> ข้อมูลการติดต่อ
          @if ($ssiRecord->contacts->count() > 0)
            <span class="badge bg-label-primary rounded-pill ms-1">{{ $ssiRecord->contacts->count() }}</span>
          @endif
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ssi" type="button">
          <i class="bx bx-star me-1"></i> ผลการประเมิน SSI
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- TAB 1 : ข้อมูลการติดต่อ --}}
      <div class="tab-pane fade show active" id="tab-contact" role="tabpanel">
        <div class="row g-4">

          {{-- ── Left: Info Panel ── --}}
          <div class="col-md-5">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
                <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="po-label">ชื่อ - นามสกุล</div>
                    <div class="info-pill fw-semibold">{{ $info['full_name'] }}</div>
                  </div>
                  <div class="col-12">
                    <div class="po-label">เบอร์โทร</div>
                    <div class="info-pill">{{ $info['phone'] }}</div>
                  </div>
                </div>
              </div>

              <div class="po-section-header" style="border-top: 1px solid #f1f5f9;">
                <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
                <h6 class="po-section-title">ข้อมูลรถ</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  <div class="col-6">
                    <div class="po-label">รุ่นหลัก</div>
                    <div class="info-pill">{{ $info['model'] }}</div>
                  </div>
                  <div class="col-6">
                    <div class="po-label">รุ่นย่อย</div>
                    <div class="info-pill">{{ $info['sub_model'] ?: '-' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="po-label">เลขถัง (VIN)</div>
                    <div class="info-pill">{{ $info['vin_number'] ?: '-' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="po-label">วันที่ส่งมอบ</div>
                    <div class="info-pill"><i class="bx bx-calendar text-muted me-2"></i>{{ $info['delivery_date'] }}
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="po-label">สถานที่ส่งมอบ</div>
                    <div class="info-pill">{{ $info['delivery_location'] ?: '-' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="po-label">จังหวัด</div>
                    <div class="info-pill">{{ $info['delivery_province'] ?: '-' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- ── Right: Contact List ══ --}}
          <div class="col-md-7">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon indigo"><i class="bx bx-phone-call"></i></div>
                <h6 class="po-section-title">รายการติดต่อ</h6>
                <div class="po-section-header-end">
                  <button type="button" class="btn btn-primary btn-sm" id="btnAddContact">
                    <i class="bx bx-plus me-1"></i> เพิ่มการติดต่อ
                  </button>
                </div>
              </div>
              <div class="po-section-body-edit">

                <div id="contactList"></div>

                <div id="noContactMsg" class="text-center py-5 text-muted">
                  <i class="bx bx-phone-off d-block mb-2 opacity-50" style="font-size: 2.5rem;"></i>
                  <span style="font-size:.88rem;">ยังไม่มีรายการติดต่อ กดปุ่ม "เพิ่มการติดต่อ" เพื่อเริ่มบันทึก</span>
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>{{-- /tab-contact --}}

      {{-- TAB 2 : ผลการประเมิน SSI --}}
      <div class="tab-pane fade" id="tab-ssi" role="tabpanel">

        @php
          $assessment = $ssiRecord->assessment;
          $payment = $ssiRecord->payment;
          $feedback = $ssiRecord->feedback;
          $resolution = $ssiRecord->resolution;

          $scoreItems = [
              ['key' => 'dw_website', 'label' => 'DW เว็บไซต์', 'icon' => 'bx bx-globe', 'color' => 'indigo'],
              [
                  'key' => 'q11_facilities',
                  'label' => 'Q11 สิ่งอำนวยความสะดวก',
                  'icon' => 'bx bx-building',
                  'color' => 'sky',
              ],
              [
                  'key' => 'q15_car_knowledge',
                  'label' => 'Q15 ความรอบรู้เกี่ยวกับรถยนต์ของที่ปรึกษาการขาย',
                  'icon' => 'bx bx-book-open',
                  'color' => 'emerald',
              ],
              [
                  'key' => 'q17_service_responsibility',
                  'label' => 'Q17 ความรับผิดชอบในการให้บริการ',
                  'icon' => 'bx bx-check-shield',
                  'color' => 'emerald',
              ],
              [
                  'key' => 'q18_sales_conditions',
                  'label' => 'Q18 การชี้แจงรายละเอียดเงื่อนไขการขาย',
                  'icon' => 'bx bx-file-find',
                  'color' => 'amber',
              ],
              [
                  'key' => 'o27_car_condition',
                  'label' => 'O27 รถที่ส่งมอบอยู่ในสภาพเรียบร้อยสมบูรณ์',
                  'icon' => 'bx bx-car',
                  'color' => 'amber',
              ],
              [
                  'key' => 'fu_followup',
                  'label' => 'FU การติดตามหลังจากส่งมอบ',
                  'icon' => 'bx bx-message-dots',
                  'color' => 'sky',
              ],
              [
                  'key' => 'recommend_showroom',
                  'label' => 'แนวโน้มที่จะแนะนำโชว์รูม',
                  'icon' => 'bx bx-like',
                  'color' => 'indigo',
              ],
              ['key' => 'sop14_test_drive', 'label' => 'SOP 14 เสนอทดลองขับ', 'icon' => 'bx bx-key', 'color' => 'pink'],
              [
                  'key' => 'sop24_update_progress',
                  'label' => 'SOP 24 แจ้งความคืบหน้า',
                  'icon' => 'bx bx-bell',
                  'color' => 'pink',
              ],
              [
                  'key' => 'sop25_accessories_complete',
                  'label' => 'SOP 25 อุปกรณ์ตกแต่งครบ',
                  'icon' => 'bx bx-wrench',
                  'color' => 'rose',
              ],
              [
                  'key' => 'sop30_satisfaction_followup',
                  'label' => 'SOP 30 ที่ปรึกษาการขายได้ติดต่อสอบถามความพึงพอใจหลังจากส่งมอบรถ',
                  'icon' => 'bx bx-phone-call',
                  'color' => 'rose',
              ],
          ];
        @endphp

        {{-- ── Card 1: ผลประเมิน SSI ── --}}
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon amber"><i class="bx bx-star"></i></div>
            <h6 class="po-section-title">ผลประเมิน SSI <small class="text-muted fw-normal ms-1">(คะแนน 1-10)</small>
            </h6>
          </div>
          <div class="po-section-body-edit">
            @foreach ($scoreItems as $item)
              <div class="score-row">
                <div class="score-row-label">
                  <i class="bx {{ $item['icon'] }} po-section-icon {{ $item['color'] }} me-2"
                    style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
                  {{ $item['label'] }}
                </div>
                <div class="score-group">
                  @for ($n = 1; $n <= 10; $n++)
                    <button type="button" class="score-btn"
                      data-val="{{ $n }}">{{ $n }}</button>
                  @endfor
                  <input type="hidden" id="score_{{ $item['key'] }}"
                    value="{{ $assessment ? $assessment->{$item['key']} ?? '' : '' }}">
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- ── Card 2: ข้อมูลยอดชำระ ── --}}
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-money"></i></div>
            <h6 class="po-section-title">ข้อมูลยอดชำระ</h6>
          </div>
          <div class="po-section-body-edit">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="mf-label form-label"><i class="bx bx-money-withdraw ci-emerald"></i> ยอดชำระ
                  (แอดมิน)</label>
                <div class="input-group">
                  <span class="input-group-text ig-emerald">฿</span>
                  <input type="text" inputmode="decimal" id="amount_admin" class="form-control amount-fmt"
                    placeholder="0.00" value="{{ $payment?->amount_admin ?? '' }}">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label"><i class="bx bx-user ci-sky"></i> ยอดชำระ (ลูกค้าแจ้ง)</label>
                <div class="input-group">
                  <span class="input-group-text ig-sky">฿</span>
                  <input type="text" inputmode="decimal" id="amount_customer" class="form-control amount-fmt"
                    placeholder="0.00" value="{{ $payment?->amount_customer ?? '' }}">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label"><i class="bx bx-transfer ci-amber"></i> Diff</label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input type="text" id="diff_display" class="form-control" readonly placeholder="—">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label"><i class="bx bx-credit-card ci-indigo"></i> ช่องทางชำระ</label>
                <div class="pay-type-group mt-1">
                  <input type="radio" name="payment_channel" id="ch_transfer" value="transfer"
                    {{ $payment?->payment_channel === 'transfer' ? 'checked' : '' }}>
                  <label for="ch_transfer"><i class="bx bx-transfer me-1"></i>โอน</label>
                  <input type="radio" name="payment_channel" id="ch_cash" value="cash"
                    {{ $payment?->payment_channel === 'cash' ? 'checked' : '' }}>
                  <label for="ch_cash"><i class="bx bx-money me-1"></i>เงินสด</label>
                </div>
              </div>
              <div class="col-md-6" id="row_transfer_correct" style="display:none;">
                <label class="mf-label form-label"><i class="bx bx-check-shield ci-emerald"></i> การโอนชำระ</label>
                <div class="yn-group mt-1">
                  <input type="radio" name="transfer_correct" id="tc_yes" value="1"
                    {{ $payment?->transfer_correct === true ? 'checked' : '' }}>
                  <label for="tc_yes"><i class="bx bx-check me-1"></i>ถูกต้อง</label>
                  <input type="radio" name="transfer_correct" id="tc_no" value="0"
                    {{ $payment?->transfer_correct === false ? 'checked' : '' }}>
                  <label for="tc_no"><i class="bx bx-x me-1"></i>ไม่ถูกต้อง</label>
                </div>
              </div>
              <div class="col-12">
                <label class="mf-label form-label"><i class="bx bx-comment-detail ci-slate"></i> หมายเหตุ</label>
                <textarea id="payment_remark" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม...">{{ $payment?->remark ?? '' }}</textarea>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Card 3: คำชม / ข้อเสนอแนะ / ร้องเรียน ── --}}
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon sky"><i class="bx bx-message-dots"></i></div>
            <h6 class="po-section-title">คำชม / ข้อเสนอแนะ / ร้องเรียน</h6>
          </div>
          <div class="po-section-body-edit">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="mf-label form-label"><i class="bx bx-like ci-emerald"></i> คำชม</label>
                <textarea id="compliment" class="form-control" rows="5" placeholder="ลูกค้าชื่นชม...">{{ $feedback?->compliment ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label"><i class="bx bx-bulb ci-amber"></i> ข้อเสนอแนะ</label>
                <textarea id="suggestion" class="form-control" rows="5" placeholder="ข้อเสนอแนะจากลูกค้า...">{{ $feedback?->suggestion ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label"><i class="bx bx-error ci-rose"></i> ร้องเรียน</label>
                <textarea id="complaint" class="form-control" rows="5" placeholder="เรื่องร้องเรียน...">{{ $feedback?->complaint ?? '' }}</textarea>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Card 4: การจัดการร้องเรียน ── --}}
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon rose"><i class="bx bx-wrench"></i></div>
            <h6 class="po-section-title">การจัดการร้องเรียน</h6>
          </div>
          <div class="po-section-body-edit">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="mf-label form-label"><i class="bx bx-comment ci-indigo"></i> หมายเหตุ <small
                    class="mf-label-note">(Comment CRO)</small></label>
                <textarea id="cro_comment" class="form-control" rows="3" placeholder="หมายเหตุจาก CRO...">{{ $resolution?->cro_comment ?? '' }}</textarea>
              </div>
              <div class="col-md-6">
                <label class="mf-label form-label"><i class="bx bx-check-double ci-emerald"></i> กรณีมีร้องเรียน
                  แก้ไขอย่างไร <small class="mf-label-note">(SM)</small></label>
                <textarea id="sm_resolution" class="form-control" rows="3" placeholder="วิธีการแก้ไขโดย SM...">{{ $resolution?->sm_resolution ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label"><i class="bx bx-calendar-check ci-emerald"></i>
                  วันที่แก้ไขปัญหา</label>
                <input type="date" id="resolution_date" class="form-control"
                  value="{{ $resolution?->resolution_date?->format('Y-m-d') ?? '' }}">
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label"><i class="bx bx-list-check ci-sky"></i> สรุปสถานะการแก้ไข</label>
                <input type="text" id="resolution_status" class="form-control" placeholder="เช่น แก้ไขเรียบร้อย"
                  value="{{ $resolution?->resolution_status ?? '' }}">
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label">
                  <i class="bx bx-send ci-amber"></i> วันที่ส่งใบแก้ไข
                  <span class="mf-label-note ms-1">(ไม่เกิน 3 วันหลัง CRO แจ้ง)</span>
                </label>
                <input type="date" id="correction_form_sent_date" class="form-control"
                  value="{{ $resolution?->correction_form_sent_date?->format('Y-m-d') ?? '' }}">
              </div>
            </div>
          </div>
        </div>

        {{-- Save Tab 2 --}}
        <div class="d-flex justify-content-end">
          <button type="button" class="btn btn-primary px-5" id="btnSaveTab2">
            <i class="bx bx-save me-1"></i> บันทึกข้อมูล
          </button>
        </div>

      </div>{{-- /tab-ssi --}}
    </div>{{-- /tab-content --}}
  </div>{{-- /nav-align-top --}}


  {{-- Modal: เพิ่มการติดต่อ --}}
  <div class="modal fade" id="modalAddContact" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow mf-content mf-content--input">

        <div class="modal-header mf-header mf-header--input px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon"><i class="bx bx-phone-call fs-5 text-white"></i></div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มรายการติดต่อ</h6>
              <small class="text-white mf-hd-sub">SSI Post-Delivery Contact</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body mf-body">

          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky"><i class="bx bx-calendar"></i></div>
              <span class="mf-section-title">วันที่และสถานะ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="mf-label form-label" for="contact_date">
                    <i class="bx bx-calendar"></i> วันที่ติดต่อ <span class="text-danger">*</span>
                  </label>
                  <input type="date" id="contact_date" class="form-control mf-input-narrow">
                </div>
                <div class="col-12">
                  <label class="mf-label form-label">
                    <i class="bx bx-phone-call"></i> สถานะการติดต่อ <span class="text-danger">*</span>
                  </label>
                  <div class="yn-group mt-1">
                    <input type="radio" name="cnt_radio" id="cnt_yes" value="1">
                    <label for="cnt_yes"><i class="bx bx-check me-1"></i>ติดต่อได้</label>
                    <input type="radio" name="cnt_radio" id="addContactNo" value="0">
                    <label for="addContactNo"><i class="bx bx-x me-1"></i>ติดต่อไม่ได้</label>
                  </div>
                </div>
                <div class="col-12" id="row_interview" style="display:none;">
                  <label class="mf-label form-label">
                    <i class="bx bx-chat"></i> ผลการสัมภาษณ์
                  </label>
                  <div class="yn-group mt-1">
                    <input type="radio" name="ssi_int_radio" id="ssi_int_yes" value="1">
                    <label for="ssi_int_yes"><i class="bx bx-check me-1"></i>เรียบร้อย</label>
                    <input type="radio" name="ssi_int_radio" id="ssi_int_no" value="0">
                    <label for="ssi_int_no"><i class="bx bx-error me-1"></i>ไม่เรียบร้อย</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-comment-detail"></i></div>
              <span class="mf-section-title">หมายเหตุ</span>
            </div>
            <div class="mf-section-body">
              <textarea id="contact_remark" class="form-control" rows="3" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="btnSaveContact">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection
