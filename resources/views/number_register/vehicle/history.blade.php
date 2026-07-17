@extends('layouts/contentNavbarLayout')
@section('title', 'ประวัติส่งเบิก / เคลียร์')

@section('content')
  <style>
    /* ธีม Sneat ใส่ border+shadow+padding ให้ .tab-content ที่ตามหลัง .nav-pills → ดูเป็นกล่องซ้อนกล่อง */
    #vehHistoryCard .tab-content {
      border: 0 !important;
      box-shadow: none !important;
      padding: 0 !important;
    }

    /* ตัวโหลดตอนเปลี่ยนเดือน (reload หน้า) */
    #histLoadingOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.35);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    #histLoadingOverlay .hist-loading-box {
      background: #fff;
      border-radius: 10px;
      padding: 24px 34px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      font-size: 0.9rem;
      color: #333;
    }
  </style>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card" id="vehHistoryCard">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-history fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ประวัติส่งเบิก / เคลียร์</div>
            <div class="text-white mf-hd-sub">Withdrawal / Clear History</div>
          </div>
          <a href="{{ route('vehicle.index') }}" class="btn btn-light btn-sm ms-auto">
            <i class="bx bx-arrow-back me-1"></i> กลับหน้าทะเบียน
          </a>
        </div>

        <div class="card-body pt-3">

          {{-- Tabs --}}
          <ul class="nav nav-pills gap-2 mb-3">
            <li class="nav-item">
              <button class="nav-link active" id="pill-withdrawal" data-bs-toggle="pill" data-bs-target="#hist-withdrawal">
                <i class="bx bx-upload me-1"></i> ส่งเบิก
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" id="pill-clear" data-bs-toggle="pill" data-bs-target="#hist-clear">
                <i class="bx bx-check-circle me-1"></i> ส่งเคลียร์
              </button>
            </li>
          </ul>

          <div class="tab-content">

            {{-- ════ ประวัติส่งเบิก ════ --}}
            <div class="tab-pane fade show active" id="hist-withdrawal">
              <div class="d-flex align-items-center gap-2 mb-3 mt-6">
                <label for="wMonthInput" class="form-label mb-0 small text-nowrap">
                  <i class="bx bx-calendar me-1"></i> เดือนที่ส่งเบิก
                </label>
                <input type="month" id="wMonthInput" class="form-control form-control-sm" style="width:170px;"
                  value="{{ $wMonth }}">
              </div>
              <div class="table-responsive">
                <table class="table table-bordered tbl-table tbl-styled histWithdrawalTable" style="width:100%;">
                  <thead class="table-success">
                    <tr>
                      <th class="text-center" style="width:60px;">No.</th>
                      <th>เลขชุด</th>
                      <th>วันที่ส่งเบิก</th>
                      <th class="text-center">จำนวน (คัน)</th>
                      <th class="text-end">ยอดรวม (฿)</th>
                      <th class="text-center">เอกสาร</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($withdrawalBatches as $i => $b)
                      <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">WD-{{ str_pad($b->withdrawal_batch, 4, '0', STR_PAD_LEFT) }}</td>
                        <td data-order="{{ $b->batch_date }}">{{ \Carbon\Carbon::parse($b->batch_date)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">{{ number_format($b->cnt) }}</td>
                        <td class="text-end">{{ number_format($b->total, 2) }}</td>
                        <td class="text-center">
                          <a href="/vehicle/export-pdf?batch={{ $b->withdrawal_batch }}" target="_blank"
                            class="btn btn-sm btn-outline-success">
                            <i class="bx bxs-file-pdf me-1"></i> ดู PDF
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>

            {{-- ════ ประวัติส่งเคลียร์ ════ --}}
            <div class="tab-pane fade" id="hist-clear">
              <div class="d-flex align-items-center gap-2 mb-3 mt-6">
                <label for="cMonthInput" class="form-label mb-0 small text-nowrap">
                  <i class="bx bx-calendar me-1"></i> เดือนที่ส่งเคลียร์
                </label>
                <input type="month" id="cMonthInput" class="form-control form-control-sm" style="width:170px;"
                  value="{{ $cMonth }}">
              </div>
              <div class="table-responsive">
                <table class="table table-bordered tbl-table tbl-styled histClearTable" style="width:100%;">
                  <thead class="table-info">
                    <tr>
                      <th class="text-center" style="width:60px;">No.</th>
                      <th>เลขชุด</th>
                      <th>วันที่ส่งเคลียร์</th>
                      <th class="text-center">จำนวน (คัน)</th>
                      <th class="text-end">ยอดรวม (฿)</th>
                      <th class="text-center">เอกสาร</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($clearBatches as $i => $b)
                      <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">CL-{{ str_pad($b->clear_batch, 4, '0', STR_PAD_LEFT) }}</td>
                        <td data-order="{{ $b->batch_date }}">{{ \Carbon\Carbon::parse($b->batch_date)->format('d/m/Y H:i') }}</td>
                        <td class="text-center">{{ number_format($b->cnt) }}</td>
                        <td class="text-end">{{ number_format($b->total, 2) }}</td>
                        <td class="text-center">
                          <a href="/vehicle/export-clear-pdf?batch={{ $b->clear_batch }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bx bxs-file-pdf me-1"></i> ดู PDF
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>

  <div id="histLoadingOverlay">
    <div class="hist-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.6rem;height:1.6rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    (function () {
      const dtOptions = {
        paging: false,   // แสดงตามเดือนอยู่แล้ว ไม่ต้องแบ่งหน้า
        info: false,     // ไม่ต้องโชว์ "แสดง x ถึง y จาก z"
        order: [[1, 'desc']], // เรียงตามเลขชุด (ล่าสุดก่อน)
        columnDefs: [{ orderable: false, targets: 5 }], // คอลัมน์เอกสาร ไม่ต้อง sort
        language: {
          search: 'ค้นหา:',
          zeroRecords: 'ไม่พบข้อมูลที่ค้นหา',
          emptyTable: 'ยังไม่มีประวัติในเดือนนี้'
        }
      };

      const $wTable = jQuery('.histWithdrawalTable').DataTable(dtOptions);
      const $cTable = jQuery('.histClearTable').DataTable(dtOptions);

      // ปรับความกว้างคอลัมน์เมื่อสลับ tab (กัน layout เพี้ยนตอน tab ซ่อนอยู่ตอน init)
      jQuery('#pill-clear').on('shown.bs.tab', () => $cTable.columns.adjust());
      jQuery('#pill-withdrawal').on('shown.bs.tab', () => $wTable.columns.adjust());

      // เปลี่ยนเดือน → reload พร้อม param ของทั้งสอง tab + คงสถานะ tab ที่เปิดอยู่
      function reload(tab) {
        document.getElementById('histLoadingOverlay').style.display = 'flex';
        const w = document.getElementById('wMonthInput').value;
        const c = document.getElementById('cMonthInput').value;
        const q = new URLSearchParams({ w_month: w, c_month: c, tab: tab });
        window.location.href = '{{ route('vehicle.history') }}?' + q.toString();
      }
      document.getElementById('wMonthInput').addEventListener('change', () => reload('withdrawal'));
      document.getElementById('cMonthInput').addEventListener('change', () => reload('clear'));

      // คงสถานะ tab หลัง reload
      const params = new URLSearchParams(window.location.search);
      if (params.get('tab') === 'clear') {
        const t = document.getElementById('pill-clear');
        if (t) t.click();
      }
    })();
  </script>
@endsection
