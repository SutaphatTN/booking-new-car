@extends('layouts/contentNavbarLayout')
@section('title', 'อัตราดอกเบี้ยวงเงิน')

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-dollar-circle fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">อัตราดอกเบี้ยวงเงิน</div>
            <div class="text-white mf-hd-sub">Floor Plan — Credit Line Interest Rate</div>
          </div>
        </div>

        <div class="card-body pt-3 fp-rate">

          {{-- ── ฟิลเตอร์งวด + brand ── --}}
          <form method="GET" action="{{ route('floor-plan.interest-rate') }}">
            <div class="fp-filter">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fp-chip fp-chip--period">
                  <i class="bx bx-calendar-event"></i> {{ $periodLabel }}
                </span>
              </div>
              <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
                <label for="month" class="fw-semibold text-muted mb-0 small text-nowrap">
                  <i class="bx bx-filter-alt"></i> เลือกงวด (เดือนที่เริ่ม)
                </label>
                <input type="month" id="month" name="month" class="form-control form-control-sm"
                  style="max-width:180px;" value="{{ $month }}">
              </div>
            </div>
            <div class="text-muted small mt-2">
              <i class="bx bx-info-circle"></i> 1 งวด = วันที่ 16 ของเดือนที่เลือก ถึงวันที่ 15 ของเดือนถัดไป
            </div>
          </form>

          @if (!$morIsThisMonth || !$spreadIsThisMonth)
            <div class="fp-note">
              <i class="bx bx-info-circle"></i>
              <span>งวดนี้ยังไม่มีการตั้งค่า — ค่าที่แสดงสืบทอดจากงวดก่อนหน้า กด <b>"บันทึก"</b> เพื่อยืนยันเป็นค่าของงวดนี้ (ไม่กระทบงวดอื่น)</span>
            </div>
          @endif

          <form id="rateForm">
            <div class="row g-4 mt-1">

              {{-- ── TISCO's MOR (ค่ากลางทุก brand) ── --}}
              <div class="col-lg-4">
                <div class="fp-mor-card h-100">
                  <div class="fp-mor-card__top">
                    <div class="fp-mor-card__icon"><i class="bx bx-bank"></i></div>
                    <div>
                      <div class="fp-mor-card__title">TISCO's MOR</div>
                      <div class="fp-mor-card__sub">ค่ากลาง — ใช้ร่วมทุก brand</div>
                    </div>
                  </div>
                  <div class="fp-mor-card__field">
                    <input type="number" step="0.01" min="0" id="mor" name="mor"
                      value="{{ number_format($mor, 2, '.', '') }}" required>
                    <span class="fp-mor-card__pct">%</span>
                  </div>
                  <div class="fp-mor-card__foot">
                    <i class="bx bx-calculator"></i> อัตราดอกเบี้ยแต่ละช่วง = MOR − ส่วนต่าง
                  </div>
                </div>
              </div>

              {{-- ── อัตราดอกเบี้ยตามช่วง aging (การ์ดแยกสี) ── --}}
              <div class="col-lg-8">
                <div class="fp-sec-title">
                  <i class="bx bx-line-chart"></i> อัตราดอกเบี้ยตามช่วง Aging
                </div>
                <div class="row g-3">
                  @php $accents = ['emerald', 'sky', 'amber', 'red']; $i = 0; @endphp
                  @foreach ($buckets as $col => $label)
                    <div class="col-sm-6">
                      <div class="fp-rate-card" data-accent="{{ $accents[$i] }}">
                        <div class="fp-rate-card__head">
                          <span class="fp-aging"><i class="bx bx-time-five"></i> Aging {{ $label }} วัน</span>
                        </div>
                        <div class="fp-rate-card__value">
                          <span class="fp-rate-num" id="rate_{{ $col }}">-</span>
                        </div>
                        <div class="fp-rate-card__spread">
                          <span class="fp-rate-card__spread-lbl">ส่วนต่าง (MOR −)</span>
                          <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0"
                              class="form-control text-end spread-input" name="{{ $col }}"
                              data-target="rate_{{ $col }}"
                              value="{{ number_format($spreads[$col], 2, '.', '') }}" required>
                            <span class="input-group-text">%</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    @php $i++; @endphp
                  @endforeach
                </div>
              </div>

            </div>

            <div class="d-flex justify-content-end gap-2 pt-4">
              <button type="submit" class="btn btn-primary px-5" id="btnSaveRate">
                <i class="bx bx-save me-1"></i>บันทึกงวดนี้
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <div id="rateLoadingOverlay">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>

  <style>
    /* loader overlay — จัดกลางจอ (id นี้ไม่ได้อยู่ใน list ของ tables.css) */
    #rateLoadingOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.35);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }

    /* ── ฟิลเตอร์ ── */
    .fp-rate .fp-filter {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: .75rem;
      background: #f7f7fb;
      border: 1px solid #ececf4;
      border-radius: .75rem;
      padding: .75rem 1rem;
    }
    .fp-rate .fp-chip {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      font-weight: 600;
      font-size: .9rem;
      padding: .4rem .8rem;
      border-radius: 2rem;
      line-height: 1;
    }
    .fp-rate .fp-chip i { font-size: 1.05rem; }
    .fp-rate .fp-chip--brand  { color: #5a3ff0; background: #ece8fe; }
    .fp-rate .fp-chip--period { color: #475569; background: #e7edf5; }

    /* ── แจ้งเตือนสืบทอดค่า ── */
    .fp-rate .fp-note {
      display: flex;
      align-items: flex-start;
      gap: .5rem;
      background: #fff7e6;
      border: 1px solid #ffe1a6;
      color: #8a5a00;
      border-radius: .75rem;
      padding: .7rem 1rem;
      font-size: .875rem;
      margin-top: 1rem;
    }
    .fp-rate .fp-note i { font-size: 1.1rem; margin-top: 1px; }

    /* ── การ์ด MOR ── */
    .fp-rate .fp-mor-card {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      border-radius: 1rem;
      padding: 1.4rem;
      color: #fff;
      background: linear-gradient(135deg, #6a5cff 0%, #8b7bff 55%, #a78bff 100%);
      box-shadow: 0 10px 24px -10px rgba(106, 92, 255, .55);
    }
    .fp-rate .fp-mor-card__top { display: flex; align-items: center; gap: .75rem; }
    .fp-rate .fp-mor-card__icon {
      width: 46px; height: 46px; flex: 0 0 46px;
      display: grid; place-items: center;
      background: rgba(255, 255, 255, .2);
      border-radius: .8rem; font-size: 1.5rem;
    }
    .fp-rate .fp-mor-card__title { font-size: 1.15rem; font-weight: 700; line-height: 1.2; }
    .fp-rate .fp-mor-card__sub   { font-size: .8rem; opacity: .85; }
    .fp-rate .fp-mor-card__field {
      display: flex; align-items: baseline; gap: .35rem;
      background: rgba(255, 255, 255, .16);
      border: 1px solid rgba(255, 255, 255, .35);
      border-radius: .75rem;
      padding: .5rem .9rem;
    }
    .fp-rate .fp-mor-card__field input {
      flex: 1 1 auto; width: 100%;
      background: transparent; border: 0; outline: none;
      color: #fff; font-size: 2.1rem; font-weight: 800;
      text-align: right; letter-spacing: -.5px;
    }
    .fp-rate .fp-mor-card__field input::-webkit-outer-spin-button,
    .fp-rate .fp-mor-card__field input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .fp-rate .fp-mor-card__pct { font-size: 1.3rem; font-weight: 700; opacity: .9; }
    .fp-rate .fp-mor-card__foot { font-size: .8rem; opacity: .9; display: flex; align-items: center; gap: .35rem; }

    /* ── หัวข้อ ── */
    .fp-rate .fp-sec-title {
      font-weight: 700; font-size: 1rem; margin-bottom: .85rem;
      display: flex; align-items: center; gap: .4rem;
    }
    .fp-rate .fp-sec-title i { color: #6a5cff; font-size: 1.2rem; }

    /* ── การ์ดอัตราดอกเบี้ยตามช่วง aging ── */
    .fp-rate .fp-rate-card {
      position: relative;
      height: 100%;
      border: 1px solid #ececf4;
      border-radius: .9rem;
      padding: 1rem 1rem 1.1rem;
      background: #fff;
      overflow: hidden;
      transition: box-shadow .15s ease, transform .15s ease;
    }
    .fp-rate .fp-rate-card:hover { box-shadow: 0 8px 20px -12px rgba(0, 0, 0, .25); transform: translateY(-1px); }
    .fp-rate .fp-rate-card::before {
      content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 5px;
      background: var(--fp-accent);
    }
    .fp-rate .fp-aging {
      display: inline-flex; align-items: center; gap: .35rem;
      font-weight: 600; font-size: .85rem;
      color: var(--fp-accent-dark);
      background: var(--fp-accent-soft);
      padding: .3rem .65rem; border-radius: 2rem;
    }
    .fp-rate .fp-rate-card__value { margin: .6rem 0 .8rem; }
    .fp-rate .fp-rate-num {
      font-size: 2.2rem; font-weight: 800; line-height: 1;
      color: var(--fp-accent-dark); letter-spacing: -1px;
    }
    .fp-rate .fp-rate-card__spread-lbl { display: block; font-size: .78rem; color: #8a8fa3; margin-bottom: .25rem; }

    .fp-rate .fp-rate-card[data-accent="emerald"] { --fp-accent: #10b981; --fp-accent-dark: #047857; --fp-accent-soft: #d1fae5; }
    .fp-rate .fp-rate-card[data-accent="sky"]     { --fp-accent: #0ea5e9; --fp-accent-dark: #0369a1; --fp-accent-soft: #e0f2fe; }
    .fp-rate .fp-rate-card[data-accent="amber"]   { --fp-accent: #f59e0b; --fp-accent-dark: #b45309; --fp-accent-soft: #fef3c7; }
    .fp-rate .fp-rate-card[data-accent="red"]     { --fp-accent: #ef4444; --fp-accent-dark: #b91c1c; --fp-accent-soft: #fee2e2; }
  </style>
@endsection

@section('page-script')
  <script>
    // เปลี่ยนเดือน -> โหลดหน้าใหม่
    $(document).on('change', '#month', function () {
      $('#rateLoadingOverlay').css('display', 'flex');
      this.form.submit();
    });
    window.addEventListener('pageshow', function () {
      $('#rateLoadingOverlay').css('display', 'none');
    });

    $(function () {
      const updateUrl = "{{ route('floor-plan.interest-rate.update') }}";
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const month = "{{ $month }}";

      // คำนวณอัตราดอกเบี้ยจริง = MOR - spread (เรียลไทม์)
      const recalc = () => {
        const mor = parseFloat($('#mor').val());
        $('.spread-input').each(function () {
          const spread = parseFloat($(this).val());
          const $out = $('#' + $(this).data('target'));
          if (isNaN(mor) || isNaN(spread)) {
            $out.text('-');
          } else {
            $out.text((mor - spread).toFixed(2) + '%');
          }
        });
      };
      $('#mor, .spread-input').on('input', recalc);
      recalc();

      // บันทึก
      $('#rateForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnSaveRate');

        const payload = {
          _method: 'PUT',
          _token: csrf,
          month: month,
          mor: $('#mor').val(),
          spread_1_60: $('input[name="spread_1_60"]').val(),
          spread_61_120: $('input[name="spread_61_120"]').val(),
          spread_121_180: $('input[name="spread_121_180"]').val(),
          spread_181_up: $('input[name="spread_181_up"]').val(),
        };

        $btn.prop('disabled', true);

        $.ajax({
          url: updateUrl,
          method: 'POST',
          data: payload,
          success: function (res) {
            Swal.fire({ icon: 'success', title: res.message ?? 'บันทึกเรียบร้อยแล้ว', timer: 1400, showConfirmButton: true });
          },
          error: function (xhr) {
            let msg = 'เกิดข้อผิดพลาด';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
              msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.status === 403) {
              msg = 'ไม่มีสิทธิ์แก้ไข';
            }
            Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
          },
          complete: function () {
            $btn.prop('disabled', false);
          },
        });
      });
    });
  </script>
@endsection
