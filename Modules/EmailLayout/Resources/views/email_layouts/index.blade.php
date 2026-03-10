@extends('layouts.app_admin')

@section('css')
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}>
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item">Definições</li>
    <li class="breadcrumb-item active">Layouts de Emails</li>
@endsection
@section('content')
    @include('top.messages')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Emails</h3>
                </div>

                <div class="card-body">
                    <table id="datatable-responsive" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Assunto</th>

                                <th style="min-width: 100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($emailTypes as $emailType)
                                <tr role="row"
                                    class="odd {{ $emailType->emailLayout()->count() == 0 ? 'font-weight-bold text-danger' : '' }}">
                                    <td>{{ $emailType->name }}</td>
                                    <td>
                                        <em>{{ $emailType->emailLayout()->first() ? $emailType->emailLayout()->first()->subject : '' }}</em>
                                    </td>
                                    <td>
                                        @can('authorization', 'editEmailLayout')
                                            <a rel='tipsy' title='Editar' class="btn btn-default space tp"
                                                href="{{ route('admin.settings.emailLayouts.edit', [$emailType->id]) }}">
                                                <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
@endsection


@section('script')
    <!-- Datatables -->
    <script src={{ asset('admin-lte/plugins/datatables/jquery.dataTables.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}></script>



    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
            $('#datatable-responsive').DataTable({
                "responsive": true,
                "language": {
                    "url": "/vendor/datatables-portuguese.json"
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": [-1]
                }]
            });
        });
    </script>
@endsection
