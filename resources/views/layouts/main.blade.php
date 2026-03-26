<!doctype html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('title', 'ZonaCapital')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
        onload="this.media='all'" />

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!-- FontAwesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Toastr notifications -->
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}" />
    <!-- InputMask no requiere CSS -->



    <!-- Sweetalert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" />
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Custom Pagination -->
    <link rel="stylesheet" href="{{ asset('plugins/custom-pagination/pagination.css') }}" />
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    <!-- Configuración global para JavaScript -->
    <script>
        window.Laravel = {
            baseUrl: '{{ url('/') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>

    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open sidebar-mini bg-body-tertiary">
    <!-- App Wrapper -->
    <div class="app-wrapper">
        <!-- Header -->
        @include('partials.header')
        <!-- End Header -->

        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!-- Sidebar Brand -->
            <div class="sidebar-brand">
                <a href="{{ route('dashboard') ?? '#' }}" class="brand-link">
                    <img src="{{ asset('adminlte/assets/img/AdminLTELogo.png') }}" alt="ZonaCapital Logo"
                        class="brand-image opacity-75 shadow" />
                    <span class="brand-text fw-light">ZonaCapital</span>
                </a>
            </div>
            <!-- End Sidebar Brand -->

            <!-- Include Menu Component -->
            @include('partials.menu')

        </aside>
        <!-- End Sidebar -->

        <!-- App Main -->
        <main class="app-main">
            <!-- App Content Header -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('header-title', 'Dashboard')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') ?? '#' }}">Inicio</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End App Content Header -->

            <!-- App Content -->
            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            <!-- End App Content -->
        </main>
        <!-- End App Main -->

        <!-- Footer -->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Version 1.0</div>
            <strong>
                Copyright &copy; {{ date('Y') }}&nbsp;
                <a href="#" class="text-decoration-none">ZonaCapital</a>.
            </strong>
            Todos los derechos reservados.
        </footer>
        <!-- End Footer -->
    </div>
    <!-- End App Wrapper -->

    <!-- Scripts -->
    <!-- jQuery PRIMERO (requerido por Bootstrap y otros plugins) -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <!-- Bootstrap DESPUÉS de jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <!-- Polyfill para Bootstrap 5 Tooltip en jQuery (requerido por Summernote) -->
    <script>
        // Polyfill: Hacer que Bootstrap 5 Tooltip funcione con jQuery
        if (window.jQuery && window.bootstrap && window.bootstrap.Tooltip) {
            (function($) {
                $.fn.tooltip = function(option) {
                    return this.each(function() {
                        var $this = $(this);
                        var data = $this.data('bs.tooltip');
                        var options = typeof option === 'object' && option;
                        if (!data) {
                            data = new bootstrap.Tooltip(this, options);
                            $this.data('bs.tooltip', data);
                        }
                        if (typeof option === 'string') {
                            if (option === 'destroy') {
                                data.dispose();
                                $this.removeData('bs.tooltip');
                            } else {
                                data[option]();
                            }
                        }
                    });
                };
                $.fn.popover = function(option) {
                    return this.each(function() {
                        var $this = $(this);
                        var data = $this.data('bs.popover');
                        var options = typeof option === 'object' && option;
                        if (!data) {
                            data = new bootstrap.Popover(this, options);
                            $this.data('bs.popover', data);
                        }
                        if (typeof option === 'string') {
                            if (option === 'destroy') {
                                data.dispose();
                                $this.removeData('bs.popover');
                            } else {
                                data[option]();
                            }
                        }
                    });
                };
            })(jQuery);
        }
    </script>

    <!-- API Laravel Function (después de jQuery) -->
    <script src="{{ asset('js/api-laravel.js') }}"></script>
    <script src="{{ asset('js/common.js') }}"></script>
    <script src="{{ asset('js/serializejson-polyfill.js') }}"></script>

    <!-- Moment.js (requerido por Tempus Dominus) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.js"></script>

    <!-- InputMask -->
    <script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>

    <!-- Toastr notifications -->
    <script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
    <!-- jQuery Validation desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/localization/messages_es.min.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css">
    <!-- Flatpickr DatePicker (Bootstrap 5 compatible) -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">

    <!-- Moment.js (requerido por Tempus Dominus) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.js"></script>

    <!-- Tempus Dominus 6 (compatible con Bootstrap 5) -->
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/js/tempus-dominus.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.7.7/dist/css/tempus-dominus.min.css">

    <!-- CSS personalizado para month picker -->
    <style>
        .flatpickr-month-picker .flatpickr-days {
            display: none !important;
        }
        .custom-months-grid .btn {
            font-size: 12px;
            padding: 5px;
        }
        .custom-months-grid .btn:hover {
            background-color: #0d6efd;
            color: white;
        }
    </style>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('adminlte/js/adminlte.js') }}"></script>

    <!-- Custom Pagination -->
    <script src="{{ asset('plugins/custom-pagination/pagination.js') }}"></script>

    <!-- OverlayScrollbars Configure -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarWrapper = document.querySelector('.sidebar-wrapper');
            if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: 'os-theme-light',
                        autoHide: 'leave',
                        clickScroll: true,
                    },
                });
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
