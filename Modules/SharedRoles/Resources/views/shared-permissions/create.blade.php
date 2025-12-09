@extends('layouts.admin')

@section('title', (isset($sharedPermission) ? 'Editar' : 'Adicionar') . ' Permissão de Partilha')

@section('breadcrumb')
    <li class="breadcrumb-item"><a class="text-dark" href="{{ route('admin.shared-permissions.index') }}">Permissões de
            partilha</a></li>
    <li class="breadcrumb-item active">{{ isset($sharedPermission) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($sharedPermission) ? route('admin.shared-permissions.update', $sharedPermission->id) : route('admin.shared-permissions.store') }}"
            method="post">
            @csrf
            @if (isset($sharedPermission))
                @method('PUT')
                <input type="hidden" id="sharedPermissionId" name="shared_permission_id" value="{{ $sharedPermission->id }}">
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
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="inputName">Código<span class="text-danger">*</span></label>
                                    <input type="text" name="code"
                                        value="{{ isset($sharedPermission) ? $sharedPermission->code : '' }}"
                                        class="validate form-control">
                                    <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                                    <span class="success valid-feedback">Campo preenchido</span>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="inputName">Nome<span class="text-danger">*</span></label>
                                    <input type="text" name="name"
                                        value="{{ isset($sharedPermission) ? $sharedPermission->name : '' }}"
                                        class="validate form-control">
                                    <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                                    <span class="success valid-feedback">Campo preenchido</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputName">Categoria<span class="text-danger">*</span></label>
                                <input type="text" name="category"
                                    value="{{ isset($sharedPermission) ? $sharedPermission->code : '' }}"
                                    class="validate form-control">
                                <span class="error invalid-feedback" id="errorFeedbackCode">Preencha este campo</span>
                                <span class="success valid-feedback">Campo preenchido</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class=" row">
                <div class="col-12">
                    <a href="{{ route('admin.shared-permissions.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit"
                        class="btn btn-success float-right">{{ isset($sharedPermission) ? 'Editar' : 'Adicionar' }}</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('script')
    <script src="/assets/admin/js/shared-permissions/form.js"></script>
    <script src="/assets/js/allForm.js"></script>
@endsection
