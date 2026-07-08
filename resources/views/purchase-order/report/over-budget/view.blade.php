@extends('layouts/contentNavbarLayout')
@section('title', 'รายงานเกินงบ')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.over-budget.modal')

@endsection
