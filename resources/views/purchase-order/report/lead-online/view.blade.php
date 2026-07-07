@extends('layouts/contentNavbarLayout')
@section('title', 'จัดสรร Lead Online')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.lead-online.modal')

@endsection
