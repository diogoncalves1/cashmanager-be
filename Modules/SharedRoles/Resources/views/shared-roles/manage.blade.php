@extends('layouts.admin')

@section('title', 'Cash Manager | Gerir Permissões do Papel de Partilha')

@section('css')
<link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a class="text-white" href="{{ route('admin.shared-roles.index') }}">Papeis de partilha</a></li>
<li class="breadcrumb-item active">Gerir Permissões</li>
@endsection

@section('content')
<section class="content">
    <form action="{{ route('admin.shared-roles.update-permissions', $sharedRole->id) }}" class="m-3" method="post">
        @csrf
        @method('PUT')
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Permissões</h3>

                </div>
                <div class="card-body">
                    @foreach($sharedPermissionsGrouped as $category => $permissions)
                    <h4><b>{{ $category }}</b></h4>
                    @foreach($permissions as $index => $permission)
                    @if($index % 4 == 0)
                    <div class="row">
                        @endif
                        <div class="col-3">
                            <div class="form-group clearfix">
                                <div class="icheck-success d-inline ">
                                    <input class="form-control" type="checkbox" name="permissions[]"
                                        value="{{ $permission->id }}" id="{{ $permission->code }}"
                                        {{ in_array($permission->id, $SharedRolePermissionsIds) ? 'checked' : '' }}>
                                    <label for="{{ $permission->code }}">{{ $permission->name }}</label>
                                </div>
                            </div>
                        </div>
                        @if(($index + 1) % 4 == 0)
                    </div>
                    @endif
                    @endforeach
                    @if(($index + 1) % 4 != 0)
                </div>
                @endif
                @endforeach
            </div>
        </div>
        </div>
        <div class=" row">
            <div class="col-12">
                <a href="{{ route('admin.shared-roles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success float-right">Guardar Alterações</button>
            </div>
        </div>
    </form>
</section>
@endsection