<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    @yield('css')
    <link rel="stylesheet" href="/admin-lte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="/admin-lte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/bs-stepper/css/bs-stepper.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/dropzone/min/dropzone.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="/admin-lte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/flag-icon-css/css/flag-icon.min.css">

</head>

<body class="dark-mode sidebar-mini layout-fixed layout-navbar-fixed  control-sidebar-slide-open accent-primary"
    style="height: auto;">

    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="/assets/images/logos/logo.png" alt="Logo" height="100" width="100">
        </div>

        @include('components.admin.header')
        @include('components.admin.sidebar')
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    @include('components.admin.notifications')
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a class="text-white"
                                        href="{{--  route('home') --}}">Home</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
            @yield('content')
        </div>
        @include('components.admin.footer')

    </div>
    <script src="/admin-lte/plugins/jquery/jquery.min.js"></script>
    <script src="/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/admin-lte/plugins/dropzone/min/dropzone.min.js"></script>
    <script src="/admin-lte/plugins/jszip/jszip.min.js"></script>
    <script src="/admin-lte/dist/js/adminlte.min.js"></script>
    <script src="/admin-lte/plugins/toastr/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/assets/js/all.js"></script>

    @yield('script')
</body>

</html>