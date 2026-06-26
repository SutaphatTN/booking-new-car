@extends('layouts/contentNavbarLayout')
@section('title', 'SSI หลังส่งมอบ')

@section('page-style')
<style>
  #row-q11-facilities.q11-hidden { display: none !important; }
</style>
@endsection

@section('page-script')
  <script>
    $(document).ready(function() {

      const salecarId = {{ $info['salecar_id'] }};

      // ── อัปเดต badge คะแนน SSI หลังบันทึก ──
      function updateSsiBadge(ssi) {
        const $b = $('#ssiScoreBadge');
        if (!$b.length || !ssi || !ssi.total) { $b.hide(); return; }
        $b.removeClass('bg-secondary bg-danger bg-success');
        if (ssi.complete && ssi.score !== null) {
          $b.addClass(ssi.score < 90 ? 'bg-danger' : 'bg-success')
            .text('คะแนนรวม ' + ssi.score + '%').show();
        } else if (ssi.answered > 0) {
          $b.addClass('bg-secondary')
            .text('กรอกแล้ว ' + ssi.answered + '/' + ssi.total + ' ข้อ').show();
        } else {
          $b.hide();
        }
      }

      // ── Score buttons ──
      $(document).on('click', '.score-btn', function() {
        const $group = $(this).closest('.score-group');
        const val = parseInt($(this).data('val'));
        $group.find('.score-btn').removeClass('selected score-low score-mid score-high');
        $(this).addClass('selected');
        if (val <= 2) $(this).addClass('score-low');
        else if (val <= 3) $(this).addClass('score-mid');
        else $(this).addClass('score-high');
        $group.find('input[type=hidden]').val(val);
      });

      // Restore saved scores on load (brand 1/3)
      $('.score-group').each(function() {
        const saved = parseInt($(this).find('input[type=hidden]').val());
        if (saved >= 1 && saved <= 5) {
          $(this).find(`.score-btn[data-val="${saved}"]`).trigger('click');
        }
      });

      // ── GWM Score buttons (1-5, brand 2) ──
      $(document).on('click', '.gwm-score-btn', function() {
        const $group = $(this).closest('.gwm-score-group');
        const val = parseInt($(this).data('val'));
        $group.find('.gwm-score-btn').removeClass('selected score-low score-mid score-high');
        $(this).addClass('selected');
        if (val <= 2) $(this).addClass('score-low');
        else if (val <= 3) $(this).addClass('score-mid');
        else $(this).addClass('score-high');
        $group.find('input.gwm-score-hidden').val(val);

        const $reasons = $(this).closest('.gwm-question').find('.gwm-reasons');
        if ($reasons.length) {
          if (val <= 2) {
            $reasons.slideDown(150);
          } else {
            $reasons.slideUp(150);
            $reasons.find('input[type=checkbox]').prop('checked', false);
            $reasons.find('.gwm-other-input').hide().val('');
          }
        }
      });

      // ── GWM "Other" checkbox toggle ──
      $(document).on('change', '.gwm-other-cb', function() {
        const $input = $(this).closest('.gwm-other-wrap').find('.gwm-other-input');
        $(this).is(':checked') ? $input.slideDown(150) : $input.slideUp(150).val('');
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
      $(document).on('hide.bs.modal', '#modalAddContact', function () {
        setTimeout(function () {
          document.activeElement.blur();
          $('body').trigger('focus');
        }, 1);
      });

      // ── บันทึกสถานที่ส่งมอบ ──
      $('#btnSaveDelivery').on('click', function () {
        const location = $('#deliveryLocation').val();
        const province = $('#deliveryProvince').val();
        const salecarId = {{ $info['salecar_id'] }};

        $.ajax({
          url: `/ssi/${salecarId}/delivery`,
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          data: { delivery_location: location, delivery_province: province },
          success: function () {
            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1200, showConfirmButton: true });
            // sync ตัวแปร deliveryLocation และ toggle Q11
            toggleQ11Row(location);
          },
          error: function () {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด' });
          }
        });
      });

      // ── Toggle Q11 เมื่อเปลี่ยน location ──
      function toggleQ11Row(location) {
        const $q11 = $('#row-q11-facilities');
        const $btns = $q11.find('.score-btn');
        if (location === 'Offsite') {
          $q11.addClass('q11-hidden');
          $('#score_q11_facilities').val('');
          $btns.prop('disabled', true).removeClass('selected score-low score-mid score-high');
        } else if (location) {
          $q11.removeClass('q11-hidden').css('opacity', '');
          $btns.prop('disabled', false);
          $('#badge-q11-unknown').hide();
        } else {
          $q11.removeClass('q11-hidden').css('opacity', '0.5');
          $btns.prop('disabled', true);
          $('#badge-q11-unknown').show();
        }
      }

      $('#deliveryLocation').on('change', function () {
        toggleQ11Row($(this).val());
      });

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

        @if ($info['brand'] == 2)
          const assessmentData = {
            gwm_q1: $('#gwm_q1').val() || null,
            gwm_q1_reasons: $('input[name="gwm_q1_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q1_other: $('#gwm_q1_other').val(),
            gwm_q2: $('#gwm_q2').val() || null,
            gwm_q2_reasons: $('input[name="gwm_q2_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q2_other: $('#gwm_q2_other').val(),
            gwm_q3: $('#gwm_q3').val() || null,
            gwm_q3_reasons: $('input[name="gwm_q3_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q3_other: $('#gwm_q3_other').val(),
            gwm_q4: $('#gwm_q4').val() || null,
            gwm_q4_reasons: $('input[name="gwm_q4_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q4_other: $('#gwm_q4_other').val(),
            gwm_q5: $('#gwm_q5').val() || null,
            gwm_q5_reasons: $('input[name="gwm_q5_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q5_other: $('#gwm_q5_other').val(),
            gwm_q6: $('#gwm_q6').val() || null,
            gwm_q6_reasons: $('input[name="gwm_q6_reasons[]"]:checked').map(function() { return $(this).val(); }).get(),
            gwm_q6_other: $('#gwm_q6_other').val(),
            gwm_q7: $('#gwm_q7').val() || null,
            gwm_q8: $('#gwm_q8').val() || null,
          };
        @else
          const deliveryLocation = $('#deliveryLocation').val() || @json($info['delivery_location']);
          const assessmentData = {
            dw_website: $('#score_dw_website').val() || null,
            q11_facilities: deliveryLocation === 'Offsite' ? null : ($('#score_q11_facilities').val() || null),
            q15_car_knowledge: $('#score_q15_car_knowledge').val() || null,
            q17_service_responsibility: $('#score_q17_service_responsibility').val() || null,
            q18_sales_conditions: $('#score_q18_sales_conditions').val() || null,
            o27_car_condition: $('#score_o27_car_condition').val() || null,
            fu_followup: $('#score_fu_followup').val() || null,
            recommend_showroom: $('#score_recommend_showroom').val() || null,
            sop14_test_drive: $('input[name="sop14_test_drive"]:checked').val() ?? null,
            sop24_update_progress: $('input[name="sop24_update_progress"]:checked').val() ?? null,
            sop25_accessories_complete: $('input[name="sop25_accessories_complete"]:checked').val() ?? null,
            sop30_satisfaction_followup: $('input[name="sop30_satisfaction_followup"]:checked').val() ?? null,
          };
        @endif

        $.ajax({
          url: `/ssi/${salecarId}/tab2`,
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ...assessmentData,
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
            const ssi = res.ssi || {};
            updateSsiBadge(ssi);

            let icon = 'success';
            let text = res.message;
            if (ssi.total > 0) {
              if (ssi.complete) {
                text = `บันทึกแล้ว · คะแนน SSI รวม ${ssi.score}%` + (ssi.score < 90 ? ' (ต่ำกว่า 90%)' : '');
              } else {
                icon = 'info';
                text = `บันทึกแล้ว · กรอกคะแนนแล้ว ${ssi.answered}/${ssi.total} ข้อ — ยังไม่ครบ จึงยังไม่สรุปคะแนนรวม`;
              }
            }
            Swal.fire({
              icon: icon,
              title: 'สำเร็จ',
              text: text,
              timer: 2200,
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
                  <div class="col-md-6">
                    <div class="po-label">รุ่นหลัก</div>
                    <div class="info-pill">{{ $info['model'] }}</div>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">รุ่นย่อย</div>
                    <div class="info-pill">{{ $info['sub_model'] ?: '-' }}</div>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">เลขถัง (VIN)</div>
                    <div class="info-pill">{{ $info['vin_number'] ?: '-' }}</div>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">วันที่ส่งมอบ</div>
                    <div class="info-pill"><i class="bx bx-calendar text-muted me-2"></i>{{ $info['delivery_date'] }}
                    </div>
                  </div>
                </div>
              </div>

              {{-- ── Card: สถานที่ส่งมอบ ── --}}
              <div class="po-section-header" style="border-top: 1px solid #f1f5f9;">
                <div class="po-section-icon amber"><i class="bx bx-map-pin"></i></div>
                <h6 class="po-section-title">สถานที่ส่งมอบ</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="po-label">สถานที่ส่งมอบ</div>
                    <select id="deliveryLocation" class="form-select form-select-sm">
                      <option value="">-- ไม่ระบุ --</option>
                      <option value="Showroom" {{ $info['delivery_location'] === 'Showroom' ? 'selected' : '' }}>Showroom</option>
                      <option value="Offsite"  {{ $info['delivery_location'] === 'Offsite'  ? 'selected' : '' }}>Offsite</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">จังหวัด</div>
                    <select id="deliveryProvince" class="form-select form-select-sm">
                      <option value="">-- ไม่ระบุ --</option>
                      @foreach ($provinces as $pv)
                        <option value="{{ $pv->id }}" {{ $info['delivery_province_id'] == $pv->id ? 'selected' : '' }}>{{ $pv->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 d-flex justify-content-end">
                    <button type="button" id="btnSaveDelivery" class="btn btn-primary btn-sm">
                      <i class="bx bx-save me-1"></i> บันทึก
                    </button>
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

          $ssiInfo    = $ssiRecord->ssiScoreInfo();
          $ssiScore   = $ssiInfo['score'];                       // null = ยังไม่ได้ประเมิน
          $ssiIsLow   = $ssiScore !== null && $ssiScore < 90;
          $hasResDate = $resolution?->resolution_date !== null;

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
          ];
          $sopItems = [
              ['key' => 'sop14_test_drive',           'label' => 'SOP 14 เสนอทดลองขับ',            'icon' => 'bx bx-key',        'color' => 'pink'],
              ['key' => 'sop24_update_progress',      'label' => 'SOP 24 แจ้งความคืบหน้า',          'icon' => 'bx bx-bell',       'color' => 'pink'],
              ['key' => 'sop25_accessories_complete',  'label' => 'SOP 25 อุปกรณ์ตกแต่งครบ',         'icon' => 'bx bx-wrench',     'color' => 'rose'],
              ['key' => 'sop30_satisfaction_followup', 'label' => 'SOP 30 ที่ปรึกษาการขายได้ติดต่อสอบถามความพึงพอใจหลังจากส่งมอบรถ', 'icon' => 'bx bx-phone-call', 'color' => 'rose'],
          ];
        @endphp

        {{-- ── Card 1: ผลประเมิน SSI ── --}}
        @if ($info['brand'] != 2)
          {{-- Brand 1 / 3 : คะแนน 1-10 --}}
          <div class="po-section-edit">
            <div class="po-section-header">
              <div class="po-section-icon amber"><i class="bx bx-star"></i></div>
              <h6 class="po-section-title">ผลประเมิน SSI <small class="text-muted fw-normal ms-1">(คะแนน 1-5)</small>
              </h6>
              @include('customer-relation.ssi._score-badge')
            </div>
            <div class="po-section-body-edit">
              @include('customer-relation.ssi._score-hint')
              @foreach ($scoreItems as $item)
                @php
                  $isQ11      = $item['key'] === 'q11_facilities';
                  $isOffsite  = $info['delivery_location'] === 'Offsite';
                  $isUnknown  = $isQ11 && !$info['delivery_location'];
                  $hideRow    = $isQ11 && $isOffsite;
                @endphp
                  <div class="score-row{{ $hideRow ? ' q11-hidden' : '' }}" {!! $isQ11 ? 'id="row-q11-facilities"' : '' !!} @if ($isUnknown) style="opacity:.5" @endif>
                    <div class="score-row-label">
                      <i class="bx {{ $item['icon'] }} po-section-icon {{ $item['color'] }} me-2"
                        style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
                      {{ $item['label'] }}
                      @if ($isUnknown)
                        <span id="badge-q11-unknown" class="badge bg-warning text-white ms-1" style="font-size:.7rem;" title="ยังไม่ระบุสถานที่ส่งมอบ">ยังไม่ระบุสถานที่</span>
                      @endif
                    </div>
                    <div class="score-group">
                      @for ($n = 1; $n <= 5; $n++)
                        <button type="button" class="score-btn"
                          data-val="{{ $n }}"{{ ($isUnknown || $hideRow) ? ' disabled' : '' }}>{{ $n }}</button>
                      @endfor
                      <input type="hidden" id="score_{{ $item['key'] }}"
                        value="{{ $assessment ? $assessment->{$item['key']} ?? '' : '' }}">
                    </div>
                  </div>
              @endforeach

              <hr class="my-3">

              @foreach ($sopItems as $sop)
                @php $sopVal = $assessment ? $assessment->{$sop['key']} : null; @endphp
                <div class="score-row">
                  <div class="score-row-label">
                    <i class="bx {{ $sop['icon'] }} po-section-icon {{ $sop['color'] }} me-2"
                      style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
                    {{ $sop['label'] }}
                  </div>
                  <div class="yn-group">
                    <input type="radio" name="{{ $sop['key'] }}" id="{{ $sop['key'] }}_yes" value="1"
                      {{ $sopVal === 1 ? 'checked' : '' }}>
                    <label for="{{ $sop['key'] }}_yes"><i class="bx bx-check me-1"></i>ใช่</label>
                    <input type="radio" name="{{ $sop['key'] }}" id="{{ $sop['key'] }}_no" value="0" class="yn-danger"
                      {{ $sopVal === 0 ? 'checked' : '' }}>
                    <label for="{{ $sop['key'] }}_no"><i class="bx bx-x me-1"></i>ไม่ใช่</label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          {{-- Brand 2 (GWM) : คะแนน 1-5 พร้อม checkbox เหตุผล --}}
          @php
            $gwmAss = $ssiRecord->assessment;
            $gwmQuestions = [
              [
                'key'   => 'q1',
                'icon'  => 'bx-user-check',
                'color' => 'sky',
                'label' => 'ท่านมีความพึงพอใจในระดับใดต่อการต้อนรับที่อบอุ่นและการดูแลอย่างใส่ใจของเจ้าหน้าที่ระหว่างการเข้ารับบริการ?',
                'reasons' => [
                  'r1' => 'ต้องรอรับบริการเป็นเวลานานหลังจากเข้ามาในโชว์รูม',
                  'r2' => 'เจ้าหน้าที่ให้การบริการไม่เป็นมิตร',
                  'r3' => 'การบริการของเจ้าหน้าที่ หรือพฤติกรรมแตกต่างกันก่อนและหลังการชำระเงิน',
                ],
              ],
              [
                'key'   => 'q2',
                'icon'  => 'bx-briefcase-alt',
                'color' => 'indigo',
                'label' => 'ท่านมีความพึงพอใจในระดับใด ต่อความสามารถของที่ปรึกษาการขาย (iAM) ในด้านการให้ข้อมูลผลิตภัณฑ์อย่างเชี่ยวชาญ และการจัดการด้านเอกสารอย่างมืออาชีพ?',
                'reasons' => [
                  'r1' => 'เจ้าหน้าที่ฝ่ายขายไม่ได้แนะนำเกี่ยวกับแบรนด์ GWM',
                  'r2' => 'เจ้าหน้าที่ฝ่ายขายนำเสนอข้อมูลเกี่ยวกับรถยนต์ได้ไม่เป็นมืออาชีพ',
                  'r3' => 'เจ้าหน้าที่ฝ่ายขายตอบคำถามของท่านได้ไม่เป็นมืออาชีพหรือไม่ครบถ้วน',
                ],
              ],
              [
                'key'   => 'q3',
                'icon'  => 'bx-key',
                'color' => 'emerald',
                'label' => 'จากประสบการณ์การทดลองขับมีส่วนช่วยให้ท่านตัดสินใจสั่งซื้อได้อย่างมั่นใจในระดับใด?',
                'reasons' => [
                  'r1' => 'การอธิบายขั้นตอนหรือข้อมูลระหว่างการทดลองขับมีความไม่เป็นมืออาชีพ',
                  'r2' => 'ระยะเวลาในการทดลองขับไม่เพียงพอ',
                  'r3' => 'เส้นทางที่ใช้ในการทดลองขับมีสั้นเกินไป หรือไม่เหมาะสม',
                  'r4' => 'ความสะอาดของรถยนต์ที่ใช้ในการทดลองขับ',
                ],
              ],
              [
                'key'   => 'q4',
                'icon'  => 'bx-car',
                'color' => 'amber',
                'label' => 'ในวันที่รับรถยนต์ใหม่ของท่าน ท่านพึงพอใจกับความสะอาดและความเรียบร้อยสมบูรณ์ของรถยนต์ใหม่มากน้อยเพียงใด?',
                'reasons' => [
                  'r1' => 'ไม่มีการจัดพิธีส่งมอบรถ ทำให้ขาดบรรยากาศหรือความเป็นพิธีการ',
                  'r2' => 'ความสะอาดของรถยนต์ที่ส่งมอบ',
                  'r3' => 'รถยนต์ใหม่ที่ส่งมอบมีร่องรอยความเสียหายหรือรอยขีดข่วน',
                ],
              ],
              [
                'key'   => 'q5',
                'icon'  => 'bx-book-open',
                'color' => 'pink',
                'label' => 'iAM ที่ดูแลท่าน ได้อธิบายรายละเอียดคุณสมบัติและการใช้งานต่าง ๆ ของตัวรถในระหว่างขั้นตอนการส่งมอบได้ชัดเจนและครบถ้วนเพียงใด?',
                'reasons' => [
                  'r1' => 'การอธิบายรายละเอียดเกี่ยวกับค่าใช้จ่ายในการซื้อรถยนต์ไม่ชัดเจน',
                  'r2' => 'ไม่มีการแนะนำหรือสาธิตฟังก์ชันหลักของรถยนต์อย่างครบถ้วน',
                  'r3' => 'ไม่มีการอธิบายข้อมูลที่เกี่ยวข้องกับการบำรุงรักษาหลังการขาย',
                ],
              ],
              [
                'key'   => 'q6',
                'icon'  => 'bx-building',
                'color' => 'rose',
                'label' => 'ท่านมีความพึงพอใจในระดับใดต่อบรรยากาศ ความสะอาด และสิ่งอำนวยความสะดวกต่าง ๆ ภายในโชว์รูม?',
                'reasons' => [
                  'r1' => 'พื้นที่ภายในโชว์รูมมีขนาดค่อนข้างจำกัด',
                  'r2' => 'ความสะอาดภายในโชว์รูม',
                  'r3' => 'อุปกรณ์หรือสิ่งอำนวยความสะดวกภายในโชว์รูมมีสภาพค่อนข้างเก่า',
                ],
              ],
            ];
            $gwmSimple = [
              ['key' => 'q7', 'icon' => 'bx-star',  'color' => 'amber',  'label' => 'ในภาพรวม ท่านมีความพึงพอใจต่อประสบการณ์การซื้อรถทั้งหมดในครั้งนี้ในระดับใด'],
              ['key' => 'q8', 'icon' => 'bx-like',  'color' => 'indigo', 'label' => 'ท่านยินดีที่จะแนะนำ (iAM) หรือศูนย์บริการ ให้เพื่อนหรือคนรู้จักมากน้อยเพียงใด'],
            ];
          @endphp

          <div class="po-section-edit">
            <div class="po-section-header">
              <div class="po-section-icon amber"><i class="bx bx-star"></i></div>
              <h6 class="po-section-title">ผลประเมิน SSI <small class="text-muted fw-normal ms-1">(คะแนน 1-5)</small></h6>
              @include('customer-relation.ssi._score-badge')
            </div>
            <div class="po-section-body-edit">

              @include('customer-relation.ssi._score-hint')
              @foreach ($gwmQuestions as $q)
                @php
                  $savedScore   = (int) ($gwmAss?->{'gwm_'.$q['key']} ?? 0);
                  $savedReasons = $gwmAss ? (json_decode($gwmAss->{'gwm_'.$q['key'].'_reasons'} ?? '[]', true) ?? []) : [];
                  $showReasons  = $savedScore >= 1 && $savedScore <= 2;
                @endphp
                <div class="gwm-question">
                  <div class="score-row">
                    <div class="score-row-label">
                      <i class="bx {{ $q['icon'] }} po-section-icon {{ $q['color'] }} me-2"
                        style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
                      {{ $q['label'] }}
                    </div>
                    <div class="gwm-score-group score-group">
                      @for ($n = 1; $n <= 5; $n++)
                        @php
                          $btnClass = 'score-btn gwm-score-btn';
                          if ($savedScore == $n) {
                            $btnClass .= ' selected';
                            $btnClass .= $n <= 2 ? ' score-low' : ($n <= 3 ? ' score-mid' : ' score-high');
                          }
                        @endphp
                        <button type="button" class="{{ $btnClass }}" data-val="{{ $n }}">{{ $n }}</button>
                      @endfor
                      <input type="hidden" id="gwm_{{ $q['key'] }}" class="gwm-score-hidden"
                        value="{{ $savedScore ?: '' }}">
                    </div>
                  </div>

                  <div class="gwm-reasons" style="{{ $showReasons ? '' : 'display:none;' }}">
                    <div class="mt-2 p-3"
                      style="margin-left:40px; background:#fffbeb; border-left:3px solid #f59e0b; border-radius:0 8px 8px 0;">
                      <div class="d-flex align-items-center gap-1 mb-2"
                        style="font-size:.82rem; font-weight:600; color:#92400e;">
                        <i class="bx bx-error-circle text-warning" style="font-size:1rem;"></i>
                        ความไม่พึงพอใจของท่าน เกิดจากสาเหตุต่อไปนี้
                        <span class="fw-normal" style="color:#78716c;">(เลือกได้หลายข้อ)</span>
                      </div>
                      @foreach ($q['reasons'] as $rval => $rlabel)
                        <div class="form-check mb-1">
                          <input class="form-check-input" type="checkbox"
                            name="gwm_{{ $q['key'] }}_reasons[]"
                            value="{{ $rval }}"
                            id="gwm_{{ $q['key'] }}_{{ $rval }}"
                            {{ in_array($rval, $savedReasons) ? 'checked' : '' }}>
                          <label class="form-check-label" for="gwm_{{ $q['key'] }}_{{ $rval }}"
                            style="font-size:.875rem; color:#374151;">{{ $rlabel }}</label>
                        </div>
                      @endforeach
                      <div class="gwm-other-wrap mt-2 pt-2" style="border-top:1px dashed #e5c87a;">
                        <div class="form-check">
                          <input class="form-check-input gwm-other-cb" type="checkbox"
                            name="gwm_{{ $q['key'] }}_reasons[]"
                            value="other"
                            id="gwm_{{ $q['key'] }}_other_cb"
                            {{ in_array('other', $savedReasons) ? 'checked' : '' }}>
                          <label class="form-check-label fw-semibold" for="gwm_{{ $q['key'] }}_other_cb"
                            style="font-size:.875rem; color:#374151;">เหตุผลอื่น ๆ โปรดระบุ:</label>
                        </div>
                        <textarea class="form-control form-control-sm mt-1 ms-4 gwm-other-input"
                          id="gwm_{{ $q['key'] }}_other"
                          rows="2"
                          placeholder="โปรดระบุเหตุผล..."
                          style="{{ in_array('other', $savedReasons) ? 'border-color:#f59e0b; resize:none;' : 'display:none; border-color:#f59e0b; resize:none;' }}">{{ $gwmAss?->{'gwm_'.$q['key'].'_other'} ?? '' }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach

              @foreach ($gwmSimple as $q)
                @php
                  $savedScore = (int) ($gwmAss?->{'gwm_'.$q['key']} ?? 0);
                @endphp
                <div class="score-row">
                  <div class="score-row-label">
                    <i class="bx {{ $q['icon'] }} po-section-icon {{ $q['color'] }} me-2"
                      style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
                    {{ $q['label'] }}
                  </div>
                  <div class="gwm-score-group score-group">
                    @for ($n = 1; $n <= 5; $n++)
                      @php
                        $btnClass = 'score-btn gwm-score-btn';
                        if ($savedScore == $n) {
                          $btnClass .= ' selected';
                          $btnClass .= $n <= 2 ? ' score-low' : ($n <= 3 ? ' score-mid' : ' score-high');
                        }
                      @endphp
                      <button type="button" class="{{ $btnClass }}" data-val="{{ $n }}">{{ $n }}</button>
                    @endfor
                    <input type="hidden" id="gwm_{{ $q['key'] }}" class="gwm-score-hidden"
                      value="{{ $savedScore ?: '' }}">
                  </div>
                </div>
              @endforeach

            </div>
          </div>
        @endif

        {{-- ── Card 2: ข้อมูลยอดชำระ ── --}}
        @if ($info['brand'] != 2)
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-money"></i></div>
            <h6 class="po-section-title">ข้อมูลยอดชำระ</h6>
          </div>
          <div class="po-section-body-edit">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="mf-label form-label" for="amount_admin"><i class="bx bx-money-withdraw ci-emerald"></i> ยอดชำระ
                  (แอดมิน)</label>
                <div class="input-group">
                  <span class="input-group-text ig-emerald">฿</span>
                  <input type="text" inputmode="decimal" id="amount_admin" class="form-control amount-fmt"
                    placeholder="0.00" value="{{ $payment?->amount_admin ?? '' }}">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label" for="amount_customer"><i class="bx bx-user ci-sky"></i> ยอดชำระ (ลูกค้าแจ้ง)</label>
                <div class="input-group">
                  <span class="input-group-text ig-sky">฿</span>
                  <input type="text" inputmode="decimal" id="amount_customer" class="form-control amount-fmt"
                    placeholder="0.00" value="{{ $payment?->amount_customer ?? '' }}">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label" for="diff_display"><i class="bx bx-transfer ci-amber"></i> Diff</label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input type="text" id="diff_display" class="form-control" readonly placeholder="—">
                </div>
              </div>
              <div class="col-md-3">
                <label class="mf-label form-label" for="ch_transfer"><i class="bx bx-credit-card ci-indigo"></i> ช่องทางชำระ</label>
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
                <label class="mf-label form-label" for="tc_yes"><i class="bx bx-check-shield ci-emerald"></i> การโอนชำระ</label>
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
                <label class="mf-label form-label" for="payment_remark"><i class="bx bx-comment-detail ci-slate"></i> หมายเหตุ</label>
                <textarea id="payment_remark" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม...">{{ $payment?->remark ?? '' }}</textarea>
              </div>
            </div>
          </div>
        </div>

        @endif

        {{-- ── Card 3: คำชม / ข้อเสนอแนะ / ร้องเรียน ── --}}
        <div class="po-section-edit">
          <div class="po-section-header">
            <div class="po-section-icon sky"><i class="bx bx-message-dots"></i></div>
            <h6 class="po-section-title">คำชม / ข้อเสนอแนะ / ร้องเรียน</h6>
          </div>
          <div class="po-section-body-edit">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="mf-label form-label" for="compliment"><i class="bx bx-like ci-emerald"></i> คำชม</label>
                <textarea id="compliment" class="form-control" rows="5" placeholder="ลูกค้าชื่นชม...">{{ $feedback?->compliment ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label" for="suggestion"><i class="bx bx-bulb ci-amber"></i> ข้อเสนอแนะ</label>
                <textarea id="suggestion" class="form-control" rows="5" placeholder="ข้อเสนอแนะจากลูกค้า...">{{ $feedback?->suggestion ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label" for="complaint"><i class="bx bx-error ci-rose"></i> ร้องเรียน</label>
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
                <label class="mf-label form-label" for="cro_comment"><i class="bx bx-comment ci-indigo"></i> หมายเหตุ <small
                    class="mf-label-note">(Comment CRO)</small></label>
                <textarea id="cro_comment" class="form-control" rows="3" placeholder="หมายเหตุจาก CRO...">{{ $resolution?->cro_comment ?? '' }}</textarea>
              </div>
              <div class="col-md-6">
                <label class="mf-label form-label" for="sm_resolution"><i class="bx bx-check-double ci-emerald"></i> กรณีมีร้องเรียน
                  แก้ไขอย่างไร <small class="mf-label-note">(SM)</small></label>
                <textarea id="sm_resolution" class="form-control" rows="3" placeholder="วิธีการแก้ไขโดย SM...">{{ $resolution?->sm_resolution ?? '' }}</textarea>
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label" for="resolution_date"><i class="bx bx-calendar-check ci-emerald"></i>
                  วันที่แก้ไขปัญหา</label>
                <input type="date" id="resolution_date" class="form-control"
                  value="{{ $resolution?->resolution_date?->format('Y-m-d') ?? '' }}">
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label" for="resolution_status"><i class="bx bx-list-check ci-sky"></i> สรุปสถานะการแก้ไข</label>
                <input type="text" id="resolution_status" class="form-control" placeholder="เช่น แก้ไขเรียบร้อย"
                  value="{{ $resolution?->resolution_status ?? '' }}">
              </div>
              <div class="col-md-4">
                <label class="mf-label form-label" for="correction_form_sent_date">
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
                <div class="col-md-5">
                  <label class="mf-label form-label" for="contact_date">
                    <i class="bx bx-calendar"></i> วันที่ติดต่อ <span class="text-danger">*</span>
                  </label>
                  <input type="date" id="contact_date" class="form-control mf-input-narrow">
                </div>
                <div class="col-md-12">
                  <label class="mf-label form-label" for="cnt_yes">
                    <i class="bx bx-phone-call"></i> สถานะการติดต่อ <span class="text-danger">*</span>
                  </label>
                  <div class="yn-group mt-1">
                    <input type="radio" name="cnt_radio" id="cnt_yes" value="1">
                    <label for="cnt_yes"><i class="bx bx-check me-1"></i>ติดต่อได้</label>
                    <input type="radio" name="cnt_radio" id="addContactNo" value="0">
                    <label for="addContactNo"><i class="bx bx-x me-1"></i>ติดต่อไม่ได้</label>
                  </div>
                </div>
                <div class="col-md-12" id="row_interview" style="display:none;">
                  <label class="mf-label form-label" for="ssi_int_yes">
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
