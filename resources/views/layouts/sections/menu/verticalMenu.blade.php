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
      @elseif (auth()->user()->brand == 4)
        <img src="{{ asset('assets/img/lepas_logo.png') }}" width="180" class="me-2">
        {{-- <span class="app-brand-text demo menu-text fw-bold ms-2">Lepas</span> --}}
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
    @php
      // manager/audit ของ Lepas(4) สลับไป Wuling(3) → โชว์เฉพาะเมนู ติดตาม/ลูกค้า/จอง (ซ่อนที่เหลือ รวมรายงาน)
      $__u = auth()->user();
      $b4CrossWuling = in_array($userRole, ['manager', 'audit'], true)
          && (int) $__u->getOriginal('brand') === 4 && (int) $__u->brand === 3;
      $wulingAllowedMenus = ['customer-tracking', 'customer', 'purchase-order'];
    @endphp
    @foreach ($menuData[0]->menu as $menu)
      @if ($b4CrossWuling && empty(array_intersect(is_array($menu->slug) ? $menu->slug : [$menu->slug], $wulingAllowedMenus)))
        @continue
      @endif
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
            'stock-film',
            'film-price-list',
            'film-usage',
            'film-settings',
        ];
      @endphp

      @if (
          in_array($userRole, ['sale', 'lead_sale']) &&
              (in_array($menu->slug, $hideForSale) ||
                  (is_array($menu->slug) && !empty(array_intersect($menu->slug, $hideForSale)))))
        @continue
      @endif

      @if ($userRole == 'registration' && $menu->slug !== 'vehicle')
        @continue
      @endif

      @php
        $menuSlugs = is_array($menu->slug) ? $menu->slug : [$menu->slug];
        $bpCsAllowed  = ['invoice', 'accessory'];
        // bp เห็นเมนู Stock Film ด้วย (cs ไม่เห็น) — รักษาพฤติกรรมเดิมหลังย้ายเมนูมา top-level
        $bpAllowed    = ['invoice', 'accessory', 'stock-film', 'film-price-list', 'film-usage', 'film-settings'];
        $croAllowed   = ['pre-delivery-inspection', 'ssi'];
      @endphp

      {{-- เมนู Floor Plan เห็นเฉพาะ role admin, audit_internal, md --}}
      @if (!empty(array_intersect($menuSlugs, ['floor-plan'])) && !in_array($userRole, ['admin', 'audit_internal', 'md']))
        @continue
      @endif

      {{-- role audit_internal เห็นแค่ ทะเบียน / การตั้งค่า / Film / การจอง-ซื้อ (เฉพาะยอดเฟิร์มเงิน FN) / รายงาน / Floor Plan --}}
      @php
        $auditInternalAllowed = [
            'vehicle',                                                          // ทะเบียน
            'model', 'model-car', 'gwm-incentive', 'car-order', 'campaign', 'finance', 'accessory', // การตั้งค่า
            'stock-film', 'film-price-list', 'film-usage', 'film-settings',     // Film
            'purchase-order',                                                   // การจอง/ซื้อ (submenu กรองเหลือยอดเฟิร์มเงิน FN)
            'report',                                                           // รายงาน
            'floor-plan',                                                       // Floor Plan
        ];
      @endphp
      @if ($userRole === 'audit_internal' && empty(array_intersect($menuSlugs, $auditInternalAllowed)))
        @continue
      @endif

      {{-- role sale ไม่เห็นเมนู ลูกค้าสัมพันธ์ (pre-delivery-inspection/ssi) และ การตลาด (source) --}}
      @if ($userRole === 'sale' && !empty(array_intersect($menuSlugs, ['pre-delivery-inspection', 'ssi', 'source'])))
        @continue
      @endif

      {{-- role marketing เห็นแค่เมนู การตลาด (source) --}}
      @if ($userRole === 'marketing' && empty(array_intersect($menuSlugs, ['source'])))
        @continue
      @endif

      @if ($userRole === 'cs' && empty(array_intersect($menuSlugs, $bpCsAllowed)))
        @continue
      @endif
      @if ($userRole === 'bp' && empty(array_intersect($menuSlugs, $bpAllowed)))
        @continue
      @endif
      @if ($userRole === 'cro' && empty(array_intersect($menuSlugs, $croAllowed)))
        @continue
      @endif

      {{-- role sp เห็นเฉพาะเมนูการตั้งค่า (Stock Film + ประดับยนต์) --}}
      @php
        $spAllowed = ['accessory', 'stock-film', 'film-price-list', 'film-usage', 'invoice'];
      @endphp
      @if ($userRole === 'sp' && empty(array_intersect($menuSlugs, $spAllowed)))
        @continue
      @endif

      @php
        // 'ad' = เมนูการตลาด > แอด (adminPage จัดการแอดได้ ; sub อื่นของ source ถูกซ่อนใน submenu)
        $adminPageAllowed = ['customer-tracking', 'customer', 'purchase-order', 'ad'];
      @endphp
      @if ($userRole === 'adminPage' && empty(array_intersect($menuSlugs, $adminPageAllowed)))
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
