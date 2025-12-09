@extends('layouts.admin')

@section('title', (isset($category) ? 'Editar' : 'Adicionar') . ' Categoria')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.categories.index') }}">Categorias</a>
    </li>
    <li class="breadcrumb-item active">{{ isset($category) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
    <link rel="stylesheet" href="/admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/admin-lte/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($category) ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}"
            method="POST">
            @csrf
            @if (isset($category))
                @method('PUT')
                <input hidden name="category_id" value="{{ $category->id }}" type="text">
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
                                <input type="hidden" name="is_default" value="1">

                                <div class="form-group col-md-6">
                                    <label>Tipo <span class="text-danger">*</span></label>
                                    <select name="type" class="select2 validate form-control" style="width: 100%"
                                        required>
                                        <option value="">Escolha o tipo</option>
                                        <option value="revenue"
                                            {{ isset($category) && $category->type == 'revenue' ? 'selected' : '' }}>
                                            {{ __('category::attributes.categories.type.revenue') }}
                                        </option>
                                        <option value="expense"
                                            {{ isset($category) && $category->type == 'expense' ? 'selected' : '' }}>
                                            {{ __('category::attributes.categories.type.expense') }}
                                        </option>
                                    </select>
                                    <span class="error invalid-feedback">Preencha este
                                        campo</span>
                                    <span class="success valid-feedback">Campo preenchido</span>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Icone</label>
                                    <input type="text" name="icon"
                                        value='{{ isset($category) ? $category->icon : '' }}' class="form-control">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Cor</label>
                                    <input type="text" name="color"
                                        class="form-control my-colorpicker1 colorpicker-element"
                                        value='{{ isset($category) ? $category->color : '' }}' data-colorpicker-id="1"
                                        data-original-title="" title="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Código</label>
                                    <input type="text" name="code"
                                        value='{{ isset($category) ? $category->code : '' }}' class="form-control">
                                </div>

                                <div class="form-group col-12">
                                    <label>Categoria Pai</label>
                                    <select name="parent_id" class="select2 form-control" style="width: 100%">
                                        <option value="">Selecione a Categoria</option>
                                        @foreach ($categories as $categoryParent)
                                            <option value="{{ $categoryParent->id }}"
                                                {{ isset($category) && $category->parent_id == $categoryParent->id ? 'selected' : '' }}>
                                                {{ $categoryParent->name->en }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                @foreach ($languages as $key => $language)
                                    <li class="nav-item"><a class="nav-link {{ $key == 0 ? 'active' : '' }}"
                                            href="#{{ $language->code }}"
                                            data-toggle="tab">{{ strtoupper($language->code) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                @foreach ($languages as $key => $language)
                                    <div class="tab-pane {{ $key == 0 ? 'active' : '' }}" id="{{ $language->code }}">
                                        <div class="form-group">
                                            <label for="inputDisplayName">Nome em {{ $language->name }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="name[{{ $language->code }}]"
                                                value="{{ isset($category->name) ? $category->name->{$language->code} ?? '' : '' }}"
                                                class="validate form-control">
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

            <div class="row">
                <div class="col-12 ">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" id="btnSubmit"
                        class="btn btn-success float-right">{{ isset($category) ? 'Editar' : 'Adicionar' }}
                        Categoria</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('script')
    <script src="/admin-lte/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="/admin-lte/plugins/select2/js/select2.full.min.js"></script>
    <script>
        $('.select2').select2();
        $('.my-colorpicker1').colorpicker()
    </script>
    <script src="/assets/js/allForm.js"></script>
@endsection
