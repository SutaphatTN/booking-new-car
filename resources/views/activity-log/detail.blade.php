<div class="modal fade logDetail" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-history fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายละเอียดการเปลี่ยนแปลง</h6>
            <small class="text-white mf-hd-sub">{{ $subject['label'] }} #{{ $log->subject_id }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-time"></i> เมื่อ</div>
            <div class="info-pill">{{ optional($log->created_at)->format('d/m/Y H:i:s') ?: '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-user"></i> ผู้ใช้</div>
            <div class="info-pill">
              {{ optional($log->user)->full_name ?: (optional($log->user)->name ?? 'ระบบ') }}
            </div>
          </div>
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-flag"></i> การกระทำ</div>
            <div class="info-pill">
              <span class="badge rounded-pill {{ $event['class'] }}">{{ $event['label'] }}</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-buildings"></i> แบรนด์</div>
            <div class="info-pill">{{ $log->brand ? ($brands[$log->brand] ?? $log->brand) : '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-globe"></i> IP</div>
            <div class="info-pill">{{ $log->ip ?: '-' }}</div>
          </div>
          <div class="col-md-4">
            <div class="po-label"><i class="bx bx-link"></i> หน้าที่แก้</div>
            <div class="info-pill" style="word-break:break-all;">{{ $log->url ?: '-' }}</div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead>
              <tr>
                <th style="width:44px;">#</th>
                <th>รายการที่เปลี่ยน</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($lines as $i => $line)
                <tr>
                  <td class="text-center text-muted">{{ $i + 1 }}</td>
                  <td>{{ $line }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="2" class="text-center text-muted">ไม่มีรายละเอียดการเปลี่ยนแปลง</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>ปิด
        </button>
      </div>

    </div>
  </div>
</div>
