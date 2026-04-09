@extends('layouts/contentNavbarLayout')
@section('title', 'Data Invoice Report')

@section('page-script')
@vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')

@include('invoice.report.modal')

@endsection