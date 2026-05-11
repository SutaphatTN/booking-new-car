@yield('vendor-script')

<!-- Sneat Vendor JS -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.min.js') }}"></script>

<!-- Sneat Main -->
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

@yield('page-script')

<!-- Your App JS (Vite only) -->
@vite(['resources/js/app.js'])

<script>
window.addEventListener('load', function () {
  const menuInner = document.querySelector('.menu-vertical .menu-inner');
  const shadow    = document.querySelector('.menu-inner-shadow');
  if (!menuInner) return;

  // Destroy PerfectScrollbar on the menu and switch to native scroll
  const menuInst = document.getElementById('layout-menu')?.menuInstance;
  if (menuInst?._scrollbar) {
    menuInst._scrollbar.destroy();
    menuInst._scrollbar = null;
  }
  if (window.Helpers?.menuPsScroll) {
    window.Helpers.menuPsScroll = null;
  }

  // Prevent manageScroll() from re-creating PS on window resize
  if (menuInst) {
    menuInst.manageScroll = function () {
      menuInner.style.overflowX = 'hidden';
      menuInner.style.overflowY = 'auto';
    };
  }

  // Re-attach shadow to native scroll event
  if (shadow) {
    menuInner.addEventListener('scroll', function () {
      shadow.style.display = this.scrollTop > 0 ? 'block' : 'none';
    });
  }
});
</script>
