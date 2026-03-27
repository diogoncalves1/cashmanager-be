@extends('layouts.admin')

@section('title', 'Tipos de Emails')

@section('css')
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}>
    <link rel="stylesheet" href={{ asset('admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item active">Tipos de Email</li>
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('admin.emailTypes.create') }}" class="btn btn-default">Adicionar Tipo de
                                Email</a>
                        </div>
                        <div class="card-body">

                            <table id="datatable-responsive" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th style="max-width: 180px;">Código</th>
                                        <th style="max-width: 180px;">Data Criação</th>
                                        <th style="min-width: 100px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($emailTypes as $emailType)
                                        <tr role="row" class="odd">
                                            <td>{{ $emailType->name }}</td>
                                            <td>{{ $emailType->code }}</td>
                                            <td>{{ $emailType->created_at }}</td>
                                            <td>
                                                <form role="form" method="post"
                                                    action="{{ route('admin.emailTypes.destroy', [$emailType->id]) }}">
                                                    @method('delete')
                                                    @csrf
                                                    <a rel='tipsy' title='Editar' class="btn btn-default space tp"
                                                        href="{{ route('admin.emailTypes.edit', [$emailType->id]) }}">
                                                        <span class="m-l-5"><i class="fa fa-pencil-alt"></i></span></a>
                                                    <button type="submit" rel='tipsy' title='Remover'
                                                        class="btn btn-times btn-default space tp">
                                                        <span class="m-l-5"><i class="fa fa-trash"></i></span></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
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
    <script src={{ asset('admin-lte/plugins/datatables/jquery.dataTables.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}></script>
    <script src={{ asset('admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}></script>

    <script>
        $(function() {
            // $('.tp').tipsy({delayIn: 500, delayOut: 10, gravity: 's'});
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
