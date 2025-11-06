@extends('layouts.admin')

@section('title', 'Permission Module | ' . (isset($permission) ? 'Editar ' : 'Adicionar ') . ' Permissão')

@section('css')
<link rel="stylesheet" href={{asset("admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css")}}>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active"><a class="text-white" href="{{ route('admin.permissions.index') }}">Permissões</a>
</li>
<li class="breadcrumb-item active">{{ isset($permission) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('content')

<section class="content">
    <form
        action="{{ isset($permission) ? route('admin.permissions.update', $permission->id) : route('admin.permissions.store')  }}"
        method="POST">
        @csrf
        @if(isset($permission))
        @method('PUT')
        <input hidden name="permission_id" value="{{ $permission->id }}" type="text">
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
                        <div class="form-group">
                            <label for="inputCode">Código <span class="text-danger">*</span></label>
                            <input type="text" name="code" value='{{ $permission->code ?? "" }}'
                                class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}" required>
                            @if ($errors->has('code'))
                            <span class="error invalid-feedback">
                                <strong>{{ $errors->first('code') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="inputDisplayName">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" value='{{ $permission->name ?? "" }}'
                                class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="inputDisplayName">Categoria <span class="text-danger">*</span></label>
                            <input type="text" name="category" value='{{ $permission->category ?? "" }}'
                                class="form-control" required>
                        </div>
                        @can('authorization', 'superAdmin')
                        <div class="icheck-danger d-inline">
                            <input type="checkbox" name="visible" id="visible" value="1" {{ isset($permission) && $permission->visible == 0 ? '' : 'checked' }}>
                            <label for="visible">Visível</label>
                        </div>
                        @endcan

                    </div>
                </div>
            </div>
        </div>
        <div class=" row">
            <div class="col-12">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" id="btnSubmit"
                    class="btn btn-success float-right">{{ isset($permission) ? 'Editar' : 'Adicionar' }}
                    Permissão</button>
            </div>
        </div>
    </form>
</section>
@endsection