@extends('layouts/contentNavbarLayout')
@section('title', 'รายงานข้อมูลประกันภัย')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.insurance.modal')

@endsection
