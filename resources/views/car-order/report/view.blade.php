@extends('layouts/contentNavbarLayout')
@section('title', 'ข้อมูลรับรถเข้า Stock')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('car-order.report.modal')

@endsection
