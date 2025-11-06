@extends('layouts.admin')

@section('title', 'Cash Manager | ' . (isset($sharedRole) ? 'Editar' : 'Adicionar') . ' Papel de Partilha')

@section('breadcrumb')
<li class="breadcrumb-item"><a class="text-white" href="{{ route('admin.shared-roles.index') }}">Papeis de partilha</a></li>
<li class="breadcrumb-item active">{{ isset($sharedRole) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('content')
<section class="content">
    <form action="{{ isset($sharedRole) ? route('admin.shared-roles.update', $sharedRole->id) : route('admin.shared-roles.store') }}" method="post">
        @csrf
        @if(isset($sharedRole))
        @method('PUT')
        <input type="hidden" name="shared_role_id" id="sharedRoleId" value="{{ $sharedRole->id }}">
        @else
        @method('POST')
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Geral</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="form-group">
                            <label for="inputName">Código<span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ isset($sharedRole) ? $sharedRole->code : '' }}" class="validate form-control" required>
                            <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                            <span class="success valid-feedback">Campo preenchido</span>
                        </div>

                    </div>
                </div>
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            @foreach($languages as $key => $language)
                            <li class="nav-item">
                                <a @class(['nav-link', "active"=> $key == 0 ])
                                    href="#{{ $language }}" data-toggle="tab">{{ strtoupper($language) }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            @foreach($languages as $key => $language)
                            <div @class(['tab-pane', "active"=> $key == 0 ]) id="{{ $language }}">
                                <div class="form-group">
                                    <label for="inputDisplayName">Nome em {{ strtoupper($language) }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" value="{{ isset($sharedRole) ? $sharedRole->name->{$language} ?? '' : '' }}"
                                        name="name[{{ $language }}]" class="validate form-control" required>
                                    <span class="error invalid-feedback">Preencha este
                                        campo</span>
                                    <span class="success valid-feedback">Campo preenchido</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class=" row">
            <div class="col-12">
                <a href="{{ route('admin.shared-roles.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success float-right">{{ isset($sharedRole) ? 'Editar' : 'Adicionar' }}</button>
            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
<script src="/assets/js/allForm.js"></script>
<script src="/assets/admin/js/shared-roles/form.js"></script>
@endsection