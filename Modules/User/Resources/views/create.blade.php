@extends('layouts.admin')

@section('title', 'User Module | ' . (isset($user) ? 'Editar' : 'Adicionar') . ' User')

@section('breadcrumb')
<li class="breadcrumb-item active"><a class="text-white" href="{{ route('admin.users.index') }}">Utilizadores</a>
</li>
<li class="breadcrumb-item active">{{ isset($user) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
<link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
<section class="content">
    <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store')  }}"
        method="POST">
        @csrf
        @if(isset($user))
        @method('PUT')
        <input hidden name="user_id" value="{{ $user->id }}" type="text">
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
                            <label for="inputCode">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" value='{{ $user->name ?? "" }}' class="validate form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="inputDisplayName">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" value='{{ $user->email ?? "" }}'
                                class="validate form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="inputDisplayName">Password
                                <span class="text-danger"> {{ isset($user) ? '' : '*'}}</span> </label>
                            <input type="password" name="password" minlength="8" class="validate form-control"
                                {{ isset($user) ? '' : 'required' }}>
                        </div>

                        <h3><b>Papeis</b> <span class="text-danger">*</span></h3>
                        @foreach($roles as $index => $role)
                        @if ($index % 4 == 0)
                        <div class="row">
                            @endif
                            <div class="col-3">
                                <div class="form-group clearfix">
                                    <div class="icheck-success d-inline ">
                                        <input class="form-control" id="role-{{$role->id}}" type="checkbox" name="roles[]"
                                            value="{{ $role->id }}"
                                            {{ isset($userRolesIds) && in_array($role->id, $userRolesIds) ? 'checked' : '' }}>
                                        <label for="role-{{$role->id}}">{{ $role->name }}</label>
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
        </div>
        <div class="row">
            <div class="col-12 ">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-success float-right">{{ isset($user) ? 'Editar' : 'Adicionar' }}
                    Utilizador</button>
            </div>
        </div>
    </form>
</section>
@endsection