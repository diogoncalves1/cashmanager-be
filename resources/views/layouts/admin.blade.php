<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | @yield('title')</title>

    <link rel="icon" href={{ asset('assets/images/favicon.ico') }}>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    @yield('css')
    <link rel="stylesheet" href="{{ asset('admin-lte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-lte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-lte/dist/css/adminlte.min.css') }}">

</head>

<body class="light-mode sidebar-mini layout-fixed layout-navbar-fixed  control-sidebar-slide-open accent-primary"
    style="height: auto;">

    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="/assets/images/logos/logo.png" alt="Logo" height="100"
                width="100">
        </div>

        @include('components.admin.header')
        @include('components.admin.sidebar')
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a class="text-dark" href="{{ route('admin.index') }}">
                                        <i class="fas fa-home mx-2"></i>Dashboard
                                    </a>
                                </li>
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </section>
            <div class="mx-3">
                @include('components.admin.notifications')
            </div>
            @yield('content')
        </div>
        @include('components.admin.footer')

    </div>
    <script src="/admin-lte/plugins/jquery/jquery.min.js"></script>
    <script src="/admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/admin-lte/dist/js/adminlte.min.js"></script>
    <script src="/assets/js/all.js"></script>

    @yield('script')
</body>

</html>
