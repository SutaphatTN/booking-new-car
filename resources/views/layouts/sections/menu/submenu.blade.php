@php
  use Illuminate\Support\Facades\Route;
  $userRole = auth()->user()->role ?? null;
  $userBrand = auth()->user()->brand ?? null;
  // manager/audit ของ Lepas(4) สลับไป Wuling(3) → ซ่อน report/export ในเมนูย่อย
  $b4CrossWuling = in_array($userRole, ['manager', 'audit'], true)
      && (int) auth()->user()->getOriginal('brand') === 4 && (int) $userBrand === 3;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      @if ($submenu->slug == 'model.color.index' && $userBrand == 1)
        @continue
      @endif

      {{-- เมนู "แอด" (การตลาด) เห็นเฉพาะ admin, adminPage --}}
      @if ($submenu->slug == 'ad.index' && !in_array($userRole, ['admin', 'adminPage']))
        @continue
      @endif

      {{-- adminPage เห็นเฉพาะ "แอด" ในเมนูการตลาด — ซ่อนแหล่งที่มาย่อย/สถานที่ --}}
      @if ($userRole === 'adminPage' && in_array($submenu->slug, ['source.sub.index', 'source.place.index']))
        @continue
      @endif

      @if ($b4CrossWuling && in_array($submenu->slug, ['purchase-order.viewCommission', 'purchase-order.viewFN', 'purchase-order.gp-setting', 'purchase-order.view-export-insurance']))
        @continue
      @endif

      @php
        $bpCsAllowed = ['accessory', 'accessory.partner', 'invoice.index', 'invoice.create', 'invoice.view-export-report'];
        $stockFilmSlugs = ['stock-film', 'film-price-list', 'film-usage'];
        $stockFilmLeaf = ['stock-film.index', 'film-price-list.index', 'film-usage.index'];

        $isAllowedForBpCs = !is_array($submenu->slug) && in_array($submenu->slug, $bpCsAllowed);

        // ให้ role = bp เห็นเมนู Stock Film (ทั้งหัวข้อหลักและเมนูย่อย) ในการตั้งค่าด้วย
        $isStockFilm = is_array($submenu->slug)
            ? !empty(array_intersect($submenu->slug, $stockFilmSlugs))
            : in_array($submenu->slug, $stockFilmLeaf);
        $isAllowedForBp = $userRole === 'bp' && $isStockFilm;

        // role sp เห็นเฉพาะเมนู Stock Film และ ประดับยนต์ (ทุกเมนูย่อย)
        $accessorySlugs = ['accessory', 'accessory.partner', 'accessory.index', 'accessory.view-export-accessory'];
        $isAccessory = !is_array($submenu->slug) && in_array($submenu->slug, $accessorySlugs);

        // role sp เห็นทุกเมนูย่อยในใบสั่งซื้อ
        $spInvoiceSlugs = ['invoice.index', 'invoice.create', 'invoice.view-export-report'];
        $isSpInvoice = !is_array($submenu->slug) && in_array($submenu->slug, $spInvoiceSlugs);
      @endphp
      @if (in_array($userRole, ['bp', 'cs']) && !$isAllowedForBpCs && !$isAllowedForBp)
        @continue
      @endif
      @if ($userRole === 'sp' && !$isStockFilm && !$isAccessory && !$isSpInvoice)
        @continue
      @endif

      {{-- เมนู "ตั้งค่า GP" เห็นเฉพาะ role admin, audit และ account --}}
      @if ($submenu->slug == 'purchase-order.gp-setting' && !in_array($userRole, ['admin', 'audit', 'audit_lead', 'gm', 'account']))
        @continue
      @endif

      {{-- เมนู "รายงานข้อมูลประกันภัย" เห็นเฉพาะ role admin --}}
      @if ($submenu->slug == 'purchase-order.view-export-insurance' && $userRole !== 'admin')
        @continue
      @endif

      {{-- เมนู "จัดสรร Lead Online" เห็นเฉพาะ role admin, gm, md, audit, manager --}}
      @if ($submenu->slug == 'purchase-order.view-export-lead-online' && !in_array($userRole, ['admin', 'gm', 'md', 'audit', 'manager']))
        @continue
      @endif

      {{-- เมนู "รายงานเกินงบ" เห็นเฉพาะ role admin, md, account, gm, manager, audit --}}
      @if ($submenu->slug == 'purchase-order.view-export-over-budget' && !in_array($userRole, ['admin', 'md', 'account', 'gm', 'manager', 'audit', 'audit_lead']))
        @continue
      @endif

      {{-- เมนู "รายงาน GP" ปิดจาก role manager --}}
      @if ($submenu->slug == 'report.gp-export' && $userRole === 'manager')
        @continue
      @endif

      {{-- เมนู "D/Bar" เห็นเฉพาะ role admin, audit, gm, manager, md --}}
      @if ($submenu->slug == 'dbar.index' && !in_array($userRole, ['admin', 'audit', 'audit_lead', 'gm', 'manager', 'md']))
        @continue
      @endif

      {{-- เมนู "ขออนุมัติเกินงบล่วงหน้า" เห็นเฉพาะ admin, audit_lead, manager, gm, md + sale/lead_sale (เห็นเฉพาะของตัวเอง) --}}
      @if ($submenu->slug == 'pre-approval.index' && !in_array($userRole, ['admin', 'audit_lead', 'manager', 'gm', 'md', 'sale', 'lead_sale']))
        @continue
      @endif

      {{-- เมนู "ค่าคอมมิชชั่น" เห็นเฉพาะ role admin, manager, gm, md --}}
      @if ($submenu->slug == 'purchase-order.viewCommission' && !in_array($userRole, ['admin', 'manager', 'gm', 'md']))
        @continue
      @endif

      @if ($submenu->slug == 'report.gwm-stock-export' && $userBrand != 2)
        @continue
      @endif

      @if (in_array($submenu->slug, ['gwm-incentive.index', 'gwm-incentive.report']) && $userBrand != 2)
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
          @if (($submenu->slug ?? '') === 'ssi.index')
            @php $ssiPending = \App\Models\SsiRecord::pendingLowScoreCount(); @endphp
            @if ($ssiPending > 0)
              <div class="badge rounded-pill bg-danger ms-auto" title="SSI ต่ำกว่า 90% ที่ยังไม่แก้ไขปัญหา">{{ $ssiPending }}</div>
            @endif
          @endif
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
