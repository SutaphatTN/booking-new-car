@extends('layouts/blankLayout')
@section('title', 'ใบส่งมอบรถแก่ลูกค้า')

@section('content')
  @php
    $customer = $saleCar->customer;
    $prefix = $customer?->prefix?->Name_TH ?? '';
    $fullName = $prefix . ($customer?->FirstName ?? '') . ' ' . ($customer?->LastName ?? '');
    $model = $saleCar->carOrder?->model?->Name_TH ?? '-';
    $color = $saleCar->displayColor ?? ($saleCar->Color ?? '');
    $consultant = $saleCar->saleUser;
    $consultantName = $consultant?->name ?? '';
    $consultantPhone = $consultant?->formatted_phone ?? '';
    $customerPhone = $customer?->formatted_mobile ?? '';
    $chassisNo = $saleCar->carOrder?->engine_number ?? '';
  @endphp

  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Sarabun', 'TH Sarabun New', sans-serif;
      font-size: 12px;
      background: #fff;
    }

    .page-wrapper {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 12mm 14mm;
      background: #fff;
    }

    .company-header {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 6px;
    }

    .company-logo {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }

    .company-info {
      flex: 1;
    }

    .company-name {
      font-size: 16px;
      font-weight: bold;
    }

    .form-title {
      text-align: center;
      font-size: 15px;
      font-weight: bold;
      margin: 8px 0 10px;
      text-decoration: underline;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }

    .info-table td {
      border: 1px solid #000;
      padding: 3px 6px;
      vertical-align: middle;
    }

    .info-table td.label {
      background: #f0f0f0;
      white-space: nowrap;
      font-weight: 500;
      width: 100px;
    }

    .info-table td.val {
      min-height: 22px;
    }

    /* เส้นสำหรับเขียนมือ */
    .write-line {
      display: inline-block;
      border-bottom: 1px solid #000;
      width: 100%;
      min-width: 80px;
      height: 18px;
      vertical-align: bottom;
    }

    .write-line.short {
      width: 60px;
    }

    .write-line.med {
      width: 90px;
    }

    .write-line.long {
      width: 150px;
    }

    /* กล่องสี่เหลี่ยมสำหรับติ๊กด้วยปากกา */
    .chk-box {
      display: inline-block;
      width: 13px;
      height: 13px;
      border: 1.5px solid #000;
      margin-right: 5px;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .section-header {
      background: #d0d0d0;
      padding: 3px 6px;
      font-weight: bold;
      border: 1px solid #000;
      border-bottom: none;
      margin-top: 6px;
    }

    .checklist-area {
      border: 1px solid #000;
      padding: 6px 8px;
    }

    .checklist-cols {
      display: flex;
      gap: 0;
    }

    .checklist-col {
      flex: 1;
      padding: 0 6px;
    }

    .checklist-col:first-child {
      border-right: 1px solid #ccc;
    }

    .check-item {
      display: flex;
      align-items: flex-start;
      gap: 0;
      margin-bottom: 4px;
      line-height: 1.4;
    }

    .check-sub {
      margin-left: 18px;
    }

    .sig-section {
      margin-top: 10px;
      border: 1px solid #000;
      padding: 10px 14px;
    }

    .sig-row {
      display: flex;
      gap: 20px;
      margin-bottom: 16px;
    }

    .sig-block {
      flex: 1;
    }

    .sig-line {
      border-bottom: 1px solid #000;
      width: 100%;
      margin-bottom: 2px;
      height: 28px;
    }

    .sig-label {
      font-size: 12px;
    }

    .customer-confirm {
      margin-top: 8px;
      border: 1px solid #000;
      padding: 8px 14px;
    }

    .customer-confirm p {
      margin: 0 0 4px;
    }

    .cus-sig-row {
      display: flex;
      gap: 40px;
      margin-top: 10px;
    }

    .cus-sig-block {
      flex: 1;
    }

    .print-btn-bar {
      text-align: right;
      margin-bottom: 10px;
    }

    @media print {
      .print-btn-bar {
        display: none;
      }

      .page-wrapper {
        padding: 6mm 10mm;
      }

      body {
        margin: 0;
      }
    }
  </style>

  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">

  <div class="page-wrapper">

    {{-- Print button --}}
    <div class="print-btn-bar">
      <button onclick="window.print()" class="btn btn-primary btn-sm">
        <i class="bx bx-printer me-1"></i> พิมพ์ฟอร์ม
      </button>
      <a href="{{ route('delivery-form.index') }}" class="btn btn-secondary btn-sm ms-2">กลับ</a>
    </div>

    {{-- Company header --}}
    <div class="company-header">
      <img src="{{ asset('assets/img/Wuling_logo.png') }}" class="company-logo" alt="Logo">
      <div class="company-info">
        <div class="company-name">บริษัท ซูเกียรติ อีวี จำกัด สำนักงานใหญ่</div>
        <div>129 หมู่ที่ 11 ถนน เพชรเกษม ต. กระบี่น้อย อ. เมือง จ. กระบี่ 81000</div>
        <div>โทร. 064-0515561</div>
      </div>
    </div>

    <div class="form-title">แบบฟอร์มการตรวจเช็คขั้นตอนสุดท้าย และใบส่งมอบรถแก่ลูกค้า</div>

    {{-- Info table --}}
    <table class="info-table">
      <tr>
        <td class="label">ชื่อลูกค้า</td>
        <td class="val" colspan="3">{{ $fullName }}</td>
        <td class="label">เบอร์โทรลูกค้า</td>
        <td class="val">{{ $customerPhone }}</td>
      </tr>
      <tr>
        <td class="label">รุ่นรถ / สีรถ</td>
        <td class="val">{{ $model }} / {{ $color }}</td>
        <td class="label">หมายเลขตัวถัง</td>
        <td class="val">{{ $chassisNo }}</td>
        <td class="label">เลขทะเบียนรถ (ป้ายแดง)</td>
        <td class="val"></td>
      </tr>
      <tr>
        <td class="label">วันที่ในการตรวจเช็ค</td>
        <td class="val"></td>
        <td class="label">เวลา</td>
        <td class="val"></td>
        <td class="label">วัน-เวลานัดหมายการส่ง</td>
        <td class="val"></td>
      </tr>
      <tr>
        <td class="label">ชื่อที่ปรึกษาการขาย</td>
        <td class="val">{{ $consultantName }}</td>
        <td class="label">โทรศัพท์</td>
        <td class="val">{{ $consultantPhone }}</td>
        <td class="label">สถานที่ส่งมอบ</td>
        <td class="val"></td>
      </tr>
    </table>

    {{-- Checklist --}}
    <div class="section-header">รายงานการตรวจสอบก่อนรับรถ</div>
    <div class="checklist-area">
      <div class="checklist-cols">

        {{-- Left column --}}
        <div class="checklist-col">
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ความสะอาดภายนอกไม่มีรอยขีดข่วนหรือบุบ, ไม่มีจุดตกพร้องเกี่ยวกับสีตัวถัง</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ความสะอาดบริเวณกระบะท้าย หรือ พื้นที่เก็บของหลังรถ</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>อุปกรณ์ประจำรถ เช่น ด้ามขันลอต, แม่แรง, ยางอะไหล่</label>
          </div>
          <div class="check-sub">
            <div class="check-item">
              <span class="chk-box"></span><label>ด้ามขันลอต</label>
              &nbsp;&nbsp;
              <span class="chk-box"></span><label>แม่แรง</label>
            </div>
            <div class="check-item">
              <span class="chk-box"></span><label>ยางอะไหล่</label>
              &nbsp;&nbsp;
              <span class="chk-box"></span><label>ชุดซ่อมยางฉุกเฉิน</label>
            </div>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ความสะอาดภายในห้องโดยสาร ไม่มีสิ่งสกปรก ฝุ่น หรือรอยเปื้อนใด ๆ</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบความสะดวกในการปรับเบาะนั่ง และเข็มขัดนิรภัย</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของเครื่องยนต์ เป็นปกติ</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบไฟเตือนบนแผงหน้าปัดแสดงครบถ้วน หลังบิดสวิทช์ที่ "ON"</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของ ก้านปัดน้ำฝน, การฉีดน้ำล้างกระจก</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการส่องสว่างของ ไฟภายในรถ, ไฟหน้า, ไฟเลี้ยว, ไฟเบรก</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ความสะอาดห้องเครื่องยนต์</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>
              ตรวจสอบกุญแจธรรมดา จำนวน <span class="write-line short"></span> ดอก,
              กุญแจรีโมท จำนวน <span class="write-line short"></span> ดอก
            </label>
          </div>
          <div class="check-item ms-3">
            <label>
              และอยู่ที่ <span class="write-line long"></span> จำนวน <span class="write-line short"></span> ดอก
            </label>
          </div>
        </div>

        {{-- Right column --}}
        <div class="checklist-col">
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการปรับตั้งนาฬิกา</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของ แตร</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของ ระบบปรับอากาศ</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของระบบเครื่องเสียง</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>
              รีโมทของระบบเครื่องเสียง จำนวน <span class="write-line short"></span> อัน
            </label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของไฟผ้าใบกระจกบังลมหลัง</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของกระจกมองข้าง</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบการทำงานของกระจกไฟฟ้า</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบความเสียหายของกระจกบังลมรอบคัน</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบลมยางทั้ง 4 ล้อตามที่กำหนดไว้ ข้างประตูรถ</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>ตรวจสอบพรมปูพื้น</label>
          </div>
          <div class="check-item">
            <span class="chk-box"></span>
            <label>การอธิบายสมุดคู่มือประจำรถ เรื่อง การเช็คระยะที่ศูนย์บริการ, การรับประกันคุณภาพรถใหม่พร้อมเงื่อนไข,
              เครือข่ายศูนย์บริการ, ตามที่ระบุไว้ในสมุดคู่มือประจำรถ</label>
          </div>
        </div>

      </div>
    </div>

    {{-- Signatures --}}
    <div class="sig-section">
      <div class="sig-row">
        <div class="sig-block">
          <div class="sig-line"></div>
          <div class="sig-label">ลายเซ็นที่ปรึกษาการขาย</div>
        </div>
        <div class="sig-block">
          <div class="sig-line"></div>
          <div class="sig-label">ลายเซ็นผู้จัดการฝ่ายขาย</div>
        </div>
      </div>
      <div class="sig-row">
        <div class="sig-block">
          <div class="sig-line"></div>
          <div class="sig-label">ลายเซ็นเจ้าหน้าที่ศูนย์บริการ</div>
        </div>
        <div class="sig-block" style="display:flex; align-items:flex-end; padding-bottom:2px;">
          <span style="font-size:12px;">(สำหรับการแนะนำในการเข้ารับบริการ)</span>
        </div>
      </div>
    </div>

    {{-- Customer confirmation --}}
    <div class="customer-confirm">
      <p>ข้าพเจ้าได้ทำการตรวจเช็คตามรายการแต่ละรายการทั้งหมดดังกล่าวข้างต้นแล้ว
        ตามบล็อก <span class="chk-box" style="display:inline-block; vertical-align:middle;"></span>
        ที่แสดงให้ทราบและได้เซ็นรับไว้เป็นหลักฐาน</p>
      <div class="cus-sig-row">
        <div class="cus-sig-block">
          <div style="border-bottom:1px solid #000; height:30px;"></div>
          <div style="font-size:12px; margin-top:3px;">ลายเซ็นท่านลูกค้า</div>
        </div>
        <div class="cus-sig-block">
          <div style="border-bottom:1px solid #000; height:30px;"></div>
          <div style="font-size:12px; margin-top:3px;">วัน/เดือน/ปี</div>
        </div>
      </div>
    </div>

  </div>
@endsection
