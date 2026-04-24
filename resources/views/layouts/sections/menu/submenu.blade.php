@php
  use Illuminate\Support\Facades\Route;
  $userRole = auth()->user()->role ?? null;
  $userBrand = auth()->user()->brand ?? null;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      @if ($submenu->slug == 'model.color.index' && $userBrand == 1)
        @continue
      @endif

      @if (in_array($userRole, ['bp', 'cs']) && !in_array($submenu->slug, ['accessory', 'accessory.partner', 'invoice.index', 'invoice.create', 'invoice.view-export-report']))
        @continue
      @endif

      @if ($submenu->slug == 'report.gwm-stock-export' && $userBrand != 2)
        @continue
      @endif

      @if (
          $userRole == 'sale' &&
              in_array($submenu->slug, [
                  'purchase-order.viewFN',
                  'purchase-order.cancellation',
                  'accessory.view-export-accessory',
              ]))
        @continue
      @endif

      {{-- @if ($submenu->slug == 'accessory.view-export-accessory' && (in_array($userRole, ['sale', 'manager']) || in_array($userBrand, [2, 3])))
        @continue
      @endif --}}

      {{-- active menu method --}}
      @php
        $activeClass = null;
        $active = 'active open';
        $currentRouteName = Route::currentRouteName();

        if ($currentRouteName === $submenu->slug) {
            $activeClass = 'active';
        } elseif (isset($submenu->submenu)) {
            if (gettype($submenu->slug) === 'array') {
                foreach ($submenu->slug as $slug) {
                    if (strpos($currentRouteName, $slug) === 0) {
                        $nextChar = substr($currentRouteName, strlen($slug), 1);
                        if ($nextChar === '' || $nextChar === '.') {
                            $activeClass = $active;
                        }
                    }
                }
            } else {
                if (strpos($currentRouteName, $submenu->slug) === 0) {
                    $nextChar = substr($currentRouteName, strlen($submenu->slug), 1);
                    if ($nextChar === '' || $nextChar === '.') {
                        $activeClass = $active;
                    }
                }
            }
        }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
          class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
          @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge rounded-pill bg-{{ $submenu->badge[0] }} text-uppercase ms-auto">{{ $submenu->badge[1] }}
            </div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
