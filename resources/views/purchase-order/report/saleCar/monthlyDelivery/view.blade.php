@extends('layouts/contentNavbarLayout')
@section('title', 'รายงานส่งมอบประจำเดือน')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.saleCar.monthlyDelivery.modal')

@endsection
