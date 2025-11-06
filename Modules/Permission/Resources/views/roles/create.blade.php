@extends('layouts.admin')

@section('title', 'Permission Module | ' . isset($role) ? 'Editar' : 'Adicionar' .' Role')

@section('breadcrumb')
<li class="breadcrumb-item active"><a class="text-white" href="{{ route('admin.roles.index') }}">Roles</a>
</li>
<li class="breadcrumb-item active">{{ isset($role) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('content')

<section class="content">
    <form action="{{ isset($role) ? route('admin.roles.update', $role->id) : route('admin.roles.store')  }}"
        method="POST">
        @csrf
        @if(isset($role))
        @method('PUT')
        <input hidden name="role_id" value="{{ $role->id }}" type="text">
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
                            <input type="text" name="code" value='{{ $role->code ?? "" }}' class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}"
                                required>
                            @if ($errors->has('code'))
                            <span class="error invalid-feedback">
                                <strong>{{ $errors->first('code') }}</strong>
                            </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="inputDisplayName">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" value='{{ $role->name ?? "" }}' class="form-control"
                                required>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class=" row">
            <div class="col-12">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" id="btnSubmit"
                    class="btn btn-success float-right">{{ isset($role) ? 'Editar' : 'Adicionar' }}
                    Papel</button>
            </div>
        </div>
    </form>
</section>
@endsection