@extends('layouts.admin')

@section('title', 'Cash Manager | Categorias')

@section('css')
<link rel="stylesheet" href="/admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="/admin-lte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active">Categorias</li>
@endsection

@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-default">
                            Adicionar Categoria
                        </a>
                    </div>
                    <div class="card-body">
                        <table id="data-table" class="table table-bordered table-striped "></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<!-- <script src="../assets/admin/js/categories/index.js"></script> -->
<script src="../assets/js/allIndex.js"></script>

<script src="/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="/admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="/admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="/admin-lte/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="/admin-lte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>

{{$dataTable->scripts()}}
@endsection