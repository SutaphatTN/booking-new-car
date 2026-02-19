@extends('layouts/contentNavbarLayout')
@section('title', 'Data GP Report')

@section('page-script')
@vite(['resources/assets/js/commission.js'])
@endsection

@section('content')

@include('purchase-order.report.gp.modal')

@endsection