@extends('layouts.app_admin_v2')

@section('page', 'Emails')

@section('css')
    <link rel="stylesheet" href="{{ asset('duralux-admin/assets/vendors/css/dataTables.bs5.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/vendors.min.css') }}">
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item">Definições</li>
    <li class="breadcrumb-item active">Layouts de Emails</li>
@endsection

@section('header-right')
    <div class="page-header-right-items">
        <div class="d-flex d-md-none">
            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                <i class="feather-arrow-left me-2"></i>
                <span>Back</span>
            </a>
        </div>
    </div>
    <div class="d-md-none d-flex align-items-center">
        <a href="javascript:void(0)" class="page-header-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="datatable-responsive" class="table table-hover" style="width: 100%;">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('duralux-admin/assets/vendors/js/dataTables.min.js') }}"></script>

    {{ $dataTable->scripts() }}
@endsection
