@extends('layouts/contentNavbarLayout')
@section('title', 'รายงานของชำร่วย')

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-gift fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รายงานของชำร่วย</div>
            <div class="text-white mf-hd-sub">Souvenir Report</div>
          </div>
        </div>

        <div class="card-body pt-4">
          <form action="{{ route('accessory.souvenir-export') }}" method="GET">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label for="from_date" class="mf-label form-label">
                  <i class="bx bx-calendar ci-amber"></i> จากวันที่ (วันที่ส่งมอบ)
                </label>
                <input type="date" id="from_date" name="from_date" class="form-control"
                  value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
              </div>
              <div class="col-md-4">
                <label for="to_date" class="mf-label form-label">
                  <i class="bx bx-calendar ci-amber"></i> ถึงวันที่
                </label>
                <input type="date" id="to_date" name="to_date" class="form-control"
                  value="{{ now()->format('Y-m-d') }}" required>
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">
                  <i class="bx bx-spreadsheet me-1"></i> ดาวน์โหลด Excel
                </button>
              </div>
            </div>
          </form>

          <div class="alert alert-info py-2 small mt-4 mb-0">
            <i class="bx bx-info-circle"></i>
            ดึงเฉพาะประดับยนต์ที่ตั้งประเภทเป็น <strong>ของชำร่วย</strong> ที่ผูกกับใบจองซึ่งมีวันที่ส่งมอบอยู่ในช่วงที่เลือก
          </div>
        </div>

      </div>
    </div>
  </div>
@endsection
