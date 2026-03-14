@extends('layouts/contentNavbarLayout')
@section('title', 'Stock GWM')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.gwm.modal')

@endsection