@extends('layouts/contentNavbarLayout')
@section('title', 'ประวัติการแก้ไข')

@section('page-script')
  @vite(['resources/assets/js/activity-log.js'])
@endsection

@section('content')
  <div class="logDetailModal"></div>

  <div id="activityLogLoadingOverlay">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-history fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ประวัติการแก้ไข</div>
            <div class="text-white mf-hd-sub">Activity Log · admin เท่านั้น</div>
          </div>
        </div>

        <div class="card-body pt-3">

          <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
            {{-- ต้องเลือกประเภทเสมอ ไม่มี "ทุกประเภท" — ดูรวมทีเดียวแล้ว log ใบจอง/รถ จะท่วมจนหาอะไรไม่เจอ --}}
            <select id="logSubject" class="form-select form-select-sm" style="width:190px;" title="ประเภทข้อมูล">
              @foreach ($subjects as $key => $s)
                <option value="{{ $key }}" {{ $key === $defaultSubject ? 'selected' : '' }}>{{ $s['label'] }}</option>
              @endforeach
            </select>

            <select id="logEvent" class="form-select form-select-sm" style="width:120px;" title="การกระทำ">
              <option value="">ทุกการกระทำ</option>
              @foreach ($events as $key => $e)
                <option value="{{ $key }}">{{ $e['label'] }}</option>
              @endforeach
            </select>

            <select id="logUser" class="form-select form-select-sm" style="width:180px;" title="ผู้ใช้">
              <option value="">ทุกคน</option>
              @foreach ($users as $u)
                <option value="{{ $u->id }}">{{ $u->full_name ?: $u->name }}</option>
              @endforeach
            </select>

            <select id="logBrand" class="form-select form-select-sm" style="width:130px;" title="แบรนด์">
              <option value="">ทุกแบรนด์</option>
              @foreach ($brands as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>

            <span class="small text-muted ms-1">ตั้งแต่</span>
            <input type="date" id="logDateFrom" class="form-control form-control-sm" style="width:150px;">
            <span class="small text-muted">ถึง</span>
            <input type="date" id="logDateTo" class="form-control form-control-sm" style="width:150px;">

            <button class="btn btn-outline-secondary btn-sm btnLogReset" data-default-subject="{{ $defaultSubject }}">
              <i class="bx bx-reset me-1"></i> ล้างตัวกรอง
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled activityLogTable">
              <thead>
                <tr>
                  <th style="width:140px;">เมื่อ</th>
                  <th style="width:150px;">ผู้ใช้</th>
                  <th style="width:120px;">ประเภท</th>
                  <th style="width:130px;">รายการ</th>
                  <th style="width:90px;">การกระทำ</th>
                  <th>สิ่งที่เปลี่ยน</th>
                  <th style="width:100px;">แบรนด์</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection
