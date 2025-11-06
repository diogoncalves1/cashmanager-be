@extends('layouts.frontend')

@section('title', 'Adicionar Categoria')

@section('breadcrumb')
<li class="breadcrumb-item active"><a class="text-white" href="{{ route('categories.index') }}">Categorias</a>
</li>
<li class="breadcrumb-item active">{{ isset($category) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
<link rel="stylesheet" href="/admin-lte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<link rel="stylesheet" href="/admin-lte/plugins/select2/css/select2.min.css">
@endsection

@section('content')
<section class="content">
    <form id="form" action="{{ isset($category) ? route('api.categories.update', $category->id) : route('api.categories.store')  }}"
        method="POST">
        @csrf
        @if(isset($category))
        <input hidden name="category_id" value="{{ $category->id }}" type="text">
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Geral</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Nome <span class="text-danger">*</span></label>
                                <input type="text" name="name" value='{{ isset($category->name) ? json_decode($category->name) : "" }}' class="validate form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="type" value='{{ $category->type ?? "" }}' class="select2 validate form-control" style="width: 100%" required>
                                    <option value="revenue">{{ __('frontend.revenue') }}</option>
                                    <option value="expense">{{ __('frontend.expense') }}</option>
                                </select>
                            </div>

                            <div class="form-group col-12">
                                <label>Categoria Pai</label>
                                <select name="parent_id"class="select2 form-control" style="width: 100%">
                                    <option value="">Selecione a Categoria</option>
                                    @foreach ($categories as $categoryParent)
                                    <option value="{{ $categoryParent->id }}" {{ isset($category) && $category->parent_id == $categoryParent->id ? "selected" : '' }}>{{ $categoryParent->name }}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 ">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Voltar</a>
                <button type="submit" id="btnSubmit" class="btn btn-success float-right">{{ isset($category) ? 'Editar' : 'Adicionar' }}
                    Categoria</button>
            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
<script src="/admin-lte/plugins/select2/js/select2.full.min.js"></script>
<script>
    $('.select2').select2();
</script>
<script src="/assets/js/allForm.js"></script>
@endsection