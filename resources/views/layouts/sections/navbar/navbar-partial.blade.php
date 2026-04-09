@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  use App\Models\TbBrand;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
  <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
    <a href="{{ url('/') }}" class="app-brand-link gap-2">
      <span class="app-brand-logo demo">@include('_partials.macros')</span>
      <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('variables.templateName') }}</span>
    </a>
  </div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
  <div
    class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base bx bx-menu icon-md"></i>
    </a>
  </div>
@endif


<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <!-- Search -->
  <!-- <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="icon-base bx bx-search icon-md"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2" placeholder="Search..." aria-label="Search...">
        </div>
    </div> -->
  <!-- /Search -->
  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Place this tag where you want the button to render. -->
    <!-- <li class="nav-item lh-1 me-4">
            <a class="github-button" href="{{ config('variables.repository') }}" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star themeselection/sneat-html-laravel-admin-template-free on GitHub">Star</a>
        </li> -->

    <!-- User -->
    @auth
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="#"
            data-bs-toggle="dropdown">
            <span class="me-2">{{ Auth::user()->username }}</span>
            <span class="avatar avatar-online" style="width: 10px; height: 10px;"></span>
          </a>
        </a>

        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <div class="d-flex">
                <!-- <div class="flex-shrink-0 me-3">
                                      <div class="avatar avatar-online">
                                          <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                      </div>
                                  </div> -->
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                  <small class="text-muted text-uppercase">{{ Auth::user()->role }}</small>
                </div>
              </div>
            </a>
          </li>
          <!-- <li>
                          <a class="dropdown-item" href="javascript:void(0);">
                              <i class="icon-base bx bx-user-circle icon-md me-3"></i><span>My Profile</span>
                          </a>
                      </li>
                      <li>
                          <a class="dropdown-item" href="javascript:void(0);">
                              <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                          </a>
                      </li> -->
          @if (Auth::user()->role == 'audit' || Auth::user()->role == 'admin' || Auth::user()->role == 'manager')
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <a class="dropdown-item" href="{{ route('register.index') }}">
                <i class="icon-base bx bx-id-card icon-md me-3"></i><span>ลงทะเบียน</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="{{ route('user.index') }}">
                <i class="icon-base bx bx-group icon-md me-3"></i><span>รายชื่อผู้ใช้งาน</span>
              </a>
            </li>

            <!-- <li>
                          <a class="dropdown-item" href="javascript:void(0);">
                              <span class="d-flex align-items-center align-middle">
                                  <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i><span class="flex-grow-1 align-middle">Billing Plan</span>
                                  <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                              </span>
                          </a>
                      </li> -->
            {{-- <li>
              <div class="dropdown-divider my-1"></div>
            </li> --}}
          @endif
          @php
            $userRole = Auth::user()->role;
            $userBrand = Auth::user()->getOriginal('brand');
            $canSwitchBrand = $userBrand != 2 && in_array($userRole, ['admin', 'account', 'audit', 'manager', 'md', 'sale', 'registration', 'bp', 'cs']);
          @endphp
          @if ($canSwitchBrand)
            <li>
              <div class="dropdown-divider my-1"></div>
            </li>
            <li>
              <div class="px-3 py-2">
                <small class="text-muted d-block mb-2">
                  <i class="bx bx-transfer-alt me-1"></i>สลับ Brand
                  @if (session('brand_switch'))
                    <span class="badge bg-warning text-dark ms-1">Active</span>
                  @endif
                </small>
                @foreach (TbBrand::all() as $tbBrand)
                  @if (!in_array($userRole, ['admin', 'account', 'audit']) && $tbBrand->id == 2)
                    @continue
                  @endif
                  @php
                    $isActive = session('brand_switch')
                        ? session('brand_switch') == $tbBrand->id
                        : $userBrand == $tbBrand->id;
                  @endphp
                  <form method="POST" action="{{ route('brand.switch') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="brand_id" value="{{ $tbBrand->id }}">
                    <button type="submit"
                      class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-secondary' }} me-1 mb-1">
                      {{ $tbBrand->name }}
                    </button>
                  </form>
                @endforeach
                @if (session('brand_switch'))
                  <form method="POST" action="{{ route('brand.reset') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger mb-1">
                      <i class="bx bx-reset me-1"></i>Reset
                    </button>
                  </form>
                @endif
              </div>
            </li>
          @endif
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a href="#" class="dropdown-item"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="icon-base bx bx-power-off icon-md me-3"></i>
              <span>Log Out</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              @csrf
            </form>
          </li>

        </ul>
      </li>
      <!--/ User -->
    @endauth
  </ul>
</div>
