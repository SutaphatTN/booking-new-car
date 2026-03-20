@extends('layouts/contentNavbarLayout')
@section('title', 'Data Accessory Partner Report')

@section('page-script')
@vite(['resources/assets/js/accessory.js'])
@endsection

@section('content')

@include('accessory.report.modal')

@endsection