@extends('layouts/contentNavbarLayout')
@section('title', 'Sale Estimate Report')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.saleCar.saleEstimate.modal')

@endsection
