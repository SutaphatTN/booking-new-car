@php
  use Illuminate\Support\Facades\Route;
  $userRole = auth()->user()->role ?? null;
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo">
    <a href="{{ url('/home') }}" class="app-brand-link">
      @if (auth()->user()->brand == 2)
        <img src="{{ asset('assets/img/Gwm_logoCrop.png') }}" width="180" class="me-2">
        {{-- <span class="app-brand-text demo menu-text fw-bold ms-2">GWM</span> --}}
      @elseif (auth()->user()->brand == 3)
        <img src="{{ asset('assets/img/Wuling_logo.png') }}" width="200" class="me-2">
        {{-- <span class="app-brand-text demo menu-text fw-bold ms-2">Wuling</span> --}}
      @else
        <img src="{{ asset('assets/img/Mitsubishi_logoCrop.png') }}" width="40" class="me-2">
        <span class="app-brand-text demo menu-text fw-bold ms-2">New Car</span>
      @endif
      <!-- <span class="app-brand-logo demo">@include('_partials.macros')</span> -->
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="icon-base bx bx-chevron-left icon-sm d-flex align-items-center justify-content-center"></i>
    </a>
  </div>

  <div class="menu-divider mt-0"></div>
  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)
      @php
        $hideForSale = [
            'model',
            'car-order',
            'accessory',
            'campaign',
            'finance',
            'report',
            'car-order.form',
            'vehicle',
            'invoice',
        ];
      @endphp

      @if (
          $userRole == 'sale' &&
              (in_array($menu->slug, $hideForSale) ||
                  (is_array($menu->slug) && !empty(array_intersect($menu->slug, $hideForSale)))))
        @continue
      @endif

      @if ($userRole == 'registration' && $menu->slug !== 'vehicle')
        @continue
      @endif

      @php
        $menuSlugs = is_array($menu->slug) ? $menu->slug : [$menu->slug];
        $bpCsAllowed = ['invoice', 'accessory'];
      @endphp
      @if (in_array($userRole, ['bp', 'cs']) && empty(array_intersect($menuSlugs, $bpCsAllowed)))
        @continue
      @endif

      @if (auth()->user()->brand == 2 && $menu->slug === 'sale.viewCommission')
        @continue
      @endif

      @if (auth()->user()->brand != 3 && in_array($menu->slug, ['delivery-form']))
        @continue
      @endif

      {{-- adding active and open class if child is active --}}

      {{-- menu headers --}}
      @if (isset($menu->menuHeader))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
        </li>
      @else
        {{-- active menu method --}}
        @php
          $activeClass = null;
          $currentRouteName = Route::currentRouteName();

          if ($currentRouteName === $menu->slug) {
              $activeClass = 'active';
          } elseif (isset($menu->submenu)) {
              if (gettype($menu->slug) === 'array') {
                  foreach ($menu->slug as $slug) {
                      if (strpos($currentRouteName, $slug) === 0) {
                          $nextChar = substr($currentRouteName, strlen($slug), 1);
                          if ($nextChar === '' || $nextChar === '.') {
                              $activeClass = 'active open';
                          }
                      }
                  }
              } else {
                  if (strpos($currentRouteName, $menu->slug) === 0) {
                      $nextChar = substr($currentRouteName, strlen($menu->slug), 1);
                      if ($nextChar === '' || $nextChar === '.') {
                          $activeClass = 'active open';
                      }
                  }
              }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
            @isset($menu->badge)
              <div class="badge rounded-pill bg-{{ $menu->badge[0] }} text-uppercase ms-auto">{{ $menu->badge[1] }}
              </div>
            @endisset
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
    @endforeach
  </ul>

</aside>
