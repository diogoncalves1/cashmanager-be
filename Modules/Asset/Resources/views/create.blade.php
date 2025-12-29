@extends('layouts.admin')

@section('title', 'Adicionar Asset')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.assets.index') }}">Assets</a>
    </li>
    <li class="breadcrumb-item active">Adicionar</li>
@endsection

@section('css')
    <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
    <section class="content">
        <form action="{{ route('admin.assets.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Geral</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="inputCode">Ticker <span class="text-danger">*</span></label>
                                <input type="text" name="ticker" class="validate form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 ">
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-success float-right">Adicionar Asset</button>
                </div>
            </div>
        </form>
    </section>
@endsection
