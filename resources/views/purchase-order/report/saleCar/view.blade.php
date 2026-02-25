@extends('layouts/contentNavbarLayout')
@section('title', 'Data Sale Car Report')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')

@include('purchase-order.report.saleCar.modal')

@endsection