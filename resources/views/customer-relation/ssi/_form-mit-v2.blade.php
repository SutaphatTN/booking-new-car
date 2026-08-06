{{--
  ฟอร์มประเมิน SSI เวอร์ชันใหม่ (brand 1/3/4 — form_version >= 2)
  เรียงแบบเดียวกับฟอร์มเดิม: ข้อที่คิดคะแนน (1-5) ไว้บน คั่นด้วยเส้น แล้วต่อด้วยข้อ SOP ที่ไม่คิดคะแนน
  คะแนนคิดจาก 5 ข้อบนเท่านั้น เต็ม 25 — ดู SsiRecord::ssiScoreFields()
--}}
@php
  $a = $assessment;

  $q1 = $a?->mit_q1;
  $q5 = $a?->mit_q5;
  $q6 = $a?->mit_q6;
  $q7 = $a?->mit_q7;
  $q8Reasons = $a ? (json_decode($a->mit_q8_reasons ?? '[]', true) ?? []) : [];
  $q8Reasons = is_array($q8Reasons) ? $q8Reasons : [];

  $q1Options = ['satisfied' => 'ประทับใจ', 'unsatisfied' => 'ไม่ประทับใจ'];
  $q5Options = [
      'showroom' => 'ที่โชว์รูม',
      'event'    => 'บูธหรือกิจกรรมต่างๆ',
      'phone'    => 'โทรศัพท์',
      'online'   => 'ออนไลน์ เช่น Line, Facebook, Website, TikTok, YouTube, Instagram etc…',
  ];
  $q6Options = ['1' => 'เข้า', '0' => 'ไม่ได้เข้า'];
  $q7Options = [
      'offered_tested'     => 'เสนอ และได้ทดลองขับ',
      'offered_not_tested' => 'เสนอ แต่ไม่ได้ทดลองขับ',
      'not_offered'        => 'ไม่ได้เสนอ',
  ];
  $q8Options = [
      'r1' => 'ไม่มีรุ่นรถที่ต้องการทดลองขับ',
      'r2' => 'ไม่มีเวลา/ไม่ว่าง/ไม่สะดวกทดลองขับ',
      'r3' => 'เคยขับของเพื่อน ญาติ หรือคนรู้จักมาแล้ว/ศึกษามาก่อนแล้ว',
      'r4' => 'มีความเชื่อมั่นในรุ่นรถอยู่แล้ว',
      'r5' => 'ตั้งใจมาออกรถอยู่แล้ว',
  ];

  // ข้อที่คิดคะแนน — ต้องตรงกับ SsiRecord::ssiScoreFields()
  $mitScoreItems = [
      ['key' => 'mit_q9',  'icon' => 'bx-coffee',           'color' => 'sky',     'label' => 'คุณภาพของสิ่งอำนวยความสะดวกสำหรับรับรองลูกค้า เช่น อาหารว่าง, เครื่องดื่ม, ฟรีอินเตอร์เน็ต'],
      ['key' => 'mit_q10', 'icon' => 'bx-book-open',        'color' => 'emerald', 'label' => 'ความรอบรู้/ความเชี่ยวชาญเกี่ยวกับรถยนต์ของที่ปรึกษาการขาย'],
      ['key' => 'mit_q11', 'icon' => 'bx-happy-heart-eyes', 'color' => 'indigo',  'label' => 'มารยาท ความกระตือรือร้นและความรับผิดชอบในการให้บริการ ทั้งต่อหน้าและผ่านช่องทางการสื่อสารอื่น ๆ'],
      ['key' => 'mit_q12', 'icon' => 'bx-file-find',        'color' => 'amber',   'label' => 'การชี้แจงรายละเอียดเงื่อนไขการขาย เช่น รายละเอียดการจอง'],
      ['key' => 'mit_q13', 'icon' => 'bx-car',              'color' => 'rose',    'label' => 'รถที่ส่งมอบอยู่ในสภาพเรียบร้อยสมบูรณ์ สะอาด ไม่มีรอยขีดข่วน'],
  ];
@endphp

<div class="po-section-edit">
  <div class="po-section-header">
    <div class="po-section-icon amber"><i class="bx bx-star"></i></div>
    <h6 class="po-section-title">ผลประเมิน SSI <small class="text-muted fw-normal ms-1">(คะแนน 1-5 · เต็ม 25)</small>
    </h6>
    @include('customer-relation.ssi._score-badge')
  </div>
  <div class="po-section-body-edit">
    @include('customer-relation.ssi._score-hint')

    <div class="mit-score-head">
      <i class="bx bx-bar-chart-alt-2 me-1"></i>
      สอบถามเกี่ยวกับประสบการณ์ครั้งล่าสุดในการซื้อรถ โดยใช้เกณฑ์การให้คะแนนความพึงพอใจ ดังนี้
      <span class="mit-hint d-block mt-1">1 คือ ควรปรับปรุง &nbsp;·&nbsp; 3 คือ พอใช้ &nbsp;·&nbsp; 5 คือ ดีเยี่ยม
        <span class="text-muted">(ให้คะแนนได้ 1-5)</span>
      </span>
    </div>

    {{-- ── ข้อที่คิดคะแนน ── --}}
    @foreach ($mitScoreItems as $item)
      <div class="score-row">
        <div class="score-row-label">
          <i class="bx {{ $item['icon'] }} po-section-icon {{ $item['color'] }} me-2"
            style="width:24px;height:24px;border-radius:6px;font-size:.8rem;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;flex-shrink:0;"></i>
          {{ $item['label'] }}
        </div>
        <div class="score-group">
          @for ($n = 1; $n <= 5; $n++)
            <button type="button" class="score-btn" data-val="{{ $n }}">{{ $n }}</button>
          @endfor
          <input type="hidden" id="score_{{ $item['key'] }}" value="{{ $a?->{$item['key']} ?? '' }}">
        </div>
      </div>
    @endforeach

    <hr class="my-3">

    {{-- ── ข้อ SOP (ไม่คิดคะแนน) ── --}}
    <div class="mit-q">
      <div class="mit-q-label"><span class="mit-sop">SOP</span>
        คุณประทับใจกับการบริการหรือสิ่งอำนวยความสะดวกของผู้จำหน่ายในการซื้อรถยนต์ครั้งล่าสุดของคุณหรือไม่?
      </div>
      <div class="mit-q-body">
        @foreach ($q1Options as $val => $label)
          <div class="form-check">
            <input class="form-check-input mit-q1-radio" type="radio" name="mit_q1" value="{{ $val }}"
              id="mit_q1_{{ $val }}" {{ $q1 === $val ? 'checked' : '' }}>
            <label class="form-check-label" for="mit_q1_{{ $val }}">{{ $label }}</label>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mit-when-satisfied" style="{{ $q1 === 'satisfied' ? '' : 'display:none;' }}">
      <div class="mit-q">
        <div class="mit-q-label"><span class="mit-sop">SOP</span> ระดับความประทับใจของท่านอยู่ในระดับใด?</div>
        <div class="mit-q-body">
          <div class="score-group">
            @for ($n = 1; $n <= 5; $n++)
              <button type="button" class="score-btn" data-val="{{ $n }}">{{ $n }}</button>
            @endfor
            <input type="hidden" id="mit_q2" value="{{ $a?->mit_q2 ?? '' }}">
          </div>
          <div class="mit-hint">1 - น้อย &nbsp;·&nbsp; 2 - เล็กน้อย &nbsp;·&nbsp; 3 - เฉยๆ &nbsp;·&nbsp; 4 - มาก
            &nbsp;·&nbsp; 5 - มากที่สุด <span class="text-muted">(ข้อนี้ไม่คิดคะแนน)</span></div>
        </div>
      </div>

      <div class="mit-q">
        <div class="mit-q-label"><span class="mit-sop">SOP</span> อะไรคือสิ่งที่คุณประทับใจ</div>
        <div class="mit-q-body">
          <textarea id="mit_q3" class="form-control" rows="2" maxlength="1000"
            placeholder="ระบุสิ่งที่ลูกค้าประทับใจ...">{{ $a?->mit_q3 }}</textarea>
        </div>
      </div>
    </div>

    <div class="mit-when-unsatisfied" style="{{ $q1 === 'unsatisfied' ? '' : 'display:none;' }}">
      <div class="mit-q">
        <div class="mit-q-label"><span class="mit-sop">SOP</span> มีอะไรบ้างที่คุณไม่ประทับใจ?</div>
        <div class="mit-q-body">
          <textarea id="mit_q4" class="form-control" rows="2" maxlength="1000"
            placeholder="ระบุสิ่งที่ลูกค้าไม่ประทับใจ...">{{ $a?->mit_q4 }}</textarea>
        </div>
      </div>
    </div>

    <div class="mit-q">
      <div class="mit-q-label"><span class="mit-sop">SOP</span>
        ช่องทางแรกที่คุณใช้ในการติดต่อเพื่อสอบถามข้อมูล หรือซื้อรถยนต์
      </div>
      <div class="mit-q-body">
        @foreach ($q5Options as $val => $label)
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mit_q5" value="{{ $val }}"
              id="mit_q5_{{ $val }}" {{ $q5 === $val ? 'checked' : '' }}>
            <label class="form-check-label" for="mit_q5_{{ $val }}">{{ $label }}</label>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mit-q">
      <div class="mit-q-label"><span class="mit-sop">SOP</span> คุณได้เข้าไปที่โชว์รูมนี้บ้างหรือไม่</div>
      <div class="mit-q-body">
        @foreach ($q6Options as $val => $label)
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mit_q6" value="{{ $val }}"
              id="mit_q6_{{ $val }}" {{ $q6 !== null && (string) $q6 === $val ? 'checked' : '' }}>
            <label class="form-check-label" for="mit_q6_{{ $val }}">{{ $label }}</label>
          </div>
        @endforeach
      </div>
    </div>

    <div class="mit-q">
      <div class="mit-q-label"><span class="mit-sop">SOP</span> ที่ปรึกษาการขายเสนอให้ทดลองขับหรือไม่</div>
      <div class="mit-q-body">
        @foreach ($q7Options as $val => $label)
          <div class="form-check">
            <input class="form-check-input mit-q7-radio" type="radio" name="mit_q7" value="{{ $val }}"
              id="mit_q7_{{ $val }}" {{ $q7 === $val ? 'checked' : '' }}>
            <label class="form-check-label" for="mit_q7_{{ $val }}">{{ $label }}</label>
          </div>
        @endforeach
      </div>
    </div>

    {{-- ถามเฉพาะเคส "เสนอ แต่ไม่ได้ทดลองขับ" --}}
    <div class="mit-q mit-when-not-tested" style="{{ $q7 === 'offered_not_tested' ? '' : 'display:none;' }}">
      <div class="mit-q-label"><span class="mit-sop">SOP</span> เหตุผลที่เสนอ แต่ไม่ได้ทดลองขับ
        <span class="fw-normal text-muted">(เลือกได้หลายข้อ)</span>
      </div>
      <div class="mit-q-body">
        @foreach ($q8Options as $val => $label)
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="mit_q8_reasons[]" value="{{ $val }}"
              id="mit_q8_{{ $val }}" {{ in_array($val, $q8Reasons) ? 'checked' : '' }}>
            <label class="form-check-label" for="mit_q8_{{ $val }}">{{ $label }}</label>
          </div>
        @endforeach
        <div class="gwm-other-wrap mt-2 pt-2" style="border-top:1px dashed #e5e7eb;">
          <div class="form-check">
            <input class="form-check-input gwm-other-cb" type="checkbox" name="mit_q8_reasons[]" value="other"
              id="mit_q8_other_cb" {{ in_array('other', $q8Reasons) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="mit_q8_other_cb">ระบุเหตุผลอื่น</label>
          </div>
          <textarea class="form-control form-control-sm mt-1 ms-4 gwm-other-input" id="mit_q8_other" rows="2"
            placeholder="โปรดระบุเหตุผล..."
            style="{{ in_array('other', $q8Reasons) ? 'resize:none;' : 'display:none; resize:none;' }}">{{ $a?->mit_q8_other }}</textarea>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .mit-q {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
  }

  .mit-q-label {
    font-size: .88rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
  }

  .mit-q-body {
    padding-left: 42px;
  }

  .mit-q-body .form-check-label {
    font-size: .875rem;
    color: #374151;
  }

  /* ป้าย SOP หน้าคำถาม — ชุดข้อที่ไม่คิดคะแนน (อยู่ใต้เส้นคั่น) */
  .mit-sop {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 20px;
    padding: 0 7px;
    margin-right: 8px;
    border-radius: 6px;
    background: #fce7f3;
    color: #be185d;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .02em;
  }

  .mit-hint {
    font-size: .78rem;
    color: #64748b;
    margin-top: 6px;
  }

  .mit-score-head {
    margin-bottom: 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border-left: 3px solid #6366f1;
    border-radius: 0 8px 8px 0;
    font-size: .85rem;
    font-weight: 600;
    color: #334155;
  }
</style>
