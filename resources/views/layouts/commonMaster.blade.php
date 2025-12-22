<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets') }}/" dir="ltr" data-skin="default" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>
        @yield('title')
    </title>
    <!-- <title>
        @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
        - {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
    </title> -->
    <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}" />
    <meta property="og:title" content="{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
    <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
    <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
    <meta property="og:description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
    <meta property="og:site_name" content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
    <meta name="robots" content="noindex, nofollow" />
    <!-- laravel CRUD token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Canonical SEO -->
    <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Mitsubishi_logoCrop32.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Mitsubishi_logoCrop16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Mitsubishi_logoCrop180.png') }}">

    <!-- Include Styles -->
    @include('layouts/sections/styles')

    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')

    <style>
        .loading-spinner {
            width: 4.5rem;
            height: 4.5rem;
        }
    </style>
</head>

<body>
    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->

    <!-- Include Scripts -->
    @include('layouts/sections/scripts')

    @auth
    <div class="modal fade" id="idleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยังใช้งานอยู่หรือไม่</h5>
                </div>
                <div class="modal-body">
                    ระบบตรวจพบว่าไม่มีการใช้งานสักพัก
                    หากไม่กดปุ่ม ระบบจะออกจากระบบอัตโนมัติ
                </div>
                <div class="modal-footer">
                    <button type="button" id="stayBtn" class="btn btn-primary">
                        ยังอยู่
                    </button>
                    <button type="button" id="logoutBtn" class="btn btn-danger">
                        ออกจากระบบ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const IDLE_LIMIT = 3 * 60;
        const LOGOUT_LIMIT = 15 * 60;

        let idleTime = 0;
        let countdownInterval = null;

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');

        function resetIdleTime() {
            idleTime = 0;
        }

        ['mousemove', 'keydown', 'click', 'scroll'].forEach(event => {
            document.addEventListener(event, resetIdleTime);
        });

        $(document).ajaxComplete(() => resetIdleTime());

        function doLogout() {
            fetch("{{ route('logout') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                }
            }).finally(() => {
                window.location.href = "{{ route('login.index') }}";
            });
        }

        function keepAlive() {
            fetch('/keep-alive');
        }

        function showIdleModal() {
            const modal = new bootstrap.Modal(document.getElementById('idleModal'));
            modal.show();
        }

        setInterval(() => {
            idleTime++;

            if (idleTime === IDLE_LIMIT) {
                showIdleModal();
            }

            if (idleTime >= LOGOUT_LIMIT) {
                doLogout();
            }
        }, 1000);

        document.getElementById('stayBtn')?.addEventListener('click', function() {
            idleTime = 0;
            bootstrap.Modal
                .getInstance(document.getElementById('idleModal'))
                .hide();

            keepAlive();
        });

        document.getElementById('logoutBtn')?.addEventListener('click', function() {
            doLogout();
        });
    </script>
    @endauth

    <div class="modal fade" id="loadingModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false">

        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-transparent border-0 d-flex align-items-center justify-content-center">

                <div class="text-center">
                    <div class="spinner-grow text-white loading-spinner"></div>
                    <div class="mt-3 text-white fw-semibold fs-5">
                        กำลังโหลดข้อมูล
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const loadingModalEl = document.getElementById('loadingModal');
            const loadingModal = new bootstrap.Modal(loadingModalEl, {
                backdrop: 'static',
                keyboard: false
            });

            let ajaxCount = 0;

            function showLoading() {
                loadingModal.show();
            }

            function hideLoading() {
                if (document.activeElement) {
                    document.activeElement.blur();
                }

                loadingModal.hide();
            }

            $(document).ajaxSend(function(event, jqxhr, settings) {
                if (settings.skipLoading || window.SKIP_NEXT_LOADING) return;

                ajaxCount++;
                showLoading();
            });

            $(document).ajaxComplete(function(event, jqxhr, settings) {
                if (!settings.skipLoading && !window.SKIP_NEXT_LOADING) {
                    ajaxCount--;
                }

                window.SKIP_NEXT_LOADING = false;

                if (ajaxCount <= 0) {
                    ajaxCount = 0;
                    hideLoading();
                }
            });

            $(document).ajaxStop(function() {
                ajaxCount = 0;
                hideLoading();
            });

            $(document).ajaxError(function() {
                ajaxCount = 0;
                hideLoading();
            });

        });
    </script>

</body>

</html>