@extends('layouts/contentNavbarLayout')
@section('title', 'ประกัน')

@section('page-script')
@vite(['resources/assets/js/insurance.js'])
@endsection

@section('content')
@php $isAdmin = auth()->user()->role === 'admin'; @endphp
<div class="inputInsuranceModal"></div>
<div class="editInsuranceModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-shield-quarter fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ประกัน</div>
          <div class="text-white mf-hd-sub">Insurance</div>
        </div>
      </div>

      <div class="card-body pt-3">

        @if ($isAdmin)
          <div class="po-filter-bar d-flex align-items-center justify-content-end">
            <button class="btn btn-secondary btn-sm btnInputInsurance">
              <i class="bx bx-plus me-1"></i> เพิ่มประกัน
            </button>
          </div>
        @endif

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled insuranceTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อประกัน</th>
                <th class="tbl-th-action" style="width:120px;">{{ $isAdmin ? 'Action' : 'สถานะ' }}</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
