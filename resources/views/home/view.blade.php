@extends('layouts/contentNavbarLayout')
@section('title', 'Home')

@section('content')
<div class="container mt-6">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow-lg border-0 text-center" style="border-radius: 20px; min-height: 70vh;">
        <div class="card-body d-flex flex-column justify-content-center align-items-center px-3">
          <img src="{{ asset('assets/img/car.jpg') }}"
            alt="Logo"
            class="img-fluid home-dashboard-logo">

          <h2 class="mt-4 fw-bold text-secondary">
            Welcome to New Caar System
          </h2>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .home-dashboard-logo {
    max-width: 100%;
    height: auto;
  }

  @media (min-width: 1200px) {
    .home-dashboard-logo {
      max-width: 500px;
    }
  }
</style>
@endsection