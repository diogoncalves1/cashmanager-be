@extends('layouts.admin')

@section('title', 'Layouts de Email')

@section('css')
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}>
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item active">Layouts de Emails</li>
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Emails</h3>
                        </div>

                        <div class="card-body">
                            <table id="datatable-responsive" class="table table-bordered table-striped">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


@section('script')
    <!-- Datatables -->
    {{ $dataTable->scripts() }}
    <script src={{ asset('admin-lte/plugins/datatables/jquery.dataTables.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}></script>
@endsection
