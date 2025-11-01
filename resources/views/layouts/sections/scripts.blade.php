@vite([
'resources/assets/vendor/libs/datatables/datatables.min.css',
'resources/assets/vendor/libs/select2/select2.min.css',
])

<!-- BEGIN: Vendor JS-->

@vite([
'resources/assets/vendor/libs/jquery/jquery.js',
'resources/assets/vendor/libs/popper/popper.js',
'resources/assets/vendor/js/bootstrap.js',
'resources/assets/vendor/libs/datatables/datatables.min.js',
'resources/assets/vendor/libs/select2/select2.min.js',
])

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->