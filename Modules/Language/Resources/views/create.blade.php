@extends('layouts.admin')

@section('title', (isset($language) ? 'Editar' : 'Adicionar') . ' Idioma')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.languages.index') }}">Idiomas</a>
    </li>
    <li class="breadcrumb-item active">{{ isset($language) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
    <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($language) ? route('admin.languages.update', $language->id) : route('admin.languages.store') }}"
            method="POST">
            @csrf
            @if (isset($language))
                @method('PUT')
                <input hidden name="language_id" value="{{ $language->id }}" type="text">
            @else
                @method('POST')
            @endif
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Geral</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="inputCode">Nome <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value='{{ $language->name ?? '' }}'
                                        class="validate form-control" required>
                                </div>

                                <div class="form-group col-6">
                                    <label for="inputDisplayName">Código <span class="text-danger">*</span></label>
                                    <input type="text" name="code" value='{{ $language->code ?? '' }}'
                                        class="validate form-control" required>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 ">
                    <a href="{{ route('admin.languages.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit"
                        class="btn btn-success float-right">{{ isset($language) ? 'Editar' : 'Adicionar' }}
                        Language</button>
                </div>
            </div>
        </form>
    </section>
@endsection
