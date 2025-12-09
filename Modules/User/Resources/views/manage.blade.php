@extends('layouts.admin')

@section('title', 'Gerir Papéis de Utilizador')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.users.index') }}">Utilizadores</a></li>
    <li class="breadcrumb-item active">Gerir Papeis</li>
@endsection

@section('css')
    <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
    <section class="content">
        <form action="{{ route('admin.users.manage.update', $id) }}" method="POST">
            @csrf
            @method('POST')
            <div class="col-12">

                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Papeis</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach ($roles as $index => $role)
                            @if ($index % 4 == 0)
                                <div class="row">
                            @endif
                            <div class="col-3">
                                <div class="form-group clearfix">
                                    <div class="icheck-success d-inline ">
                                        <input class="form-control" id="role-{{ $role->id }}" type="checkbox"
                                            name="roles[]" value="{{ $role->id }}"
                                            {{ isset($userRolesIds) && in_array($role->id, $userRolesIds) ? 'checked' : '' }}>
                                        <label for="role-{{ $role->id }}"><?= $role->name ?></label>
                                    </div>
                                </div>
                            </div>
                            @if (($index + 1) % 4 == 0)
                    </div>
                    @endif
                    @endforeach
                    @if (($index + 1) % 4 != 0)
                </div>
                @endif
            </div>
            </div>
            </div>
            <div class=" row">
                <div class="col-12">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-success float-right">Guardar
                        Alterações</button>
                </div>
            </div>
        </form>
    </section>
@endsection
