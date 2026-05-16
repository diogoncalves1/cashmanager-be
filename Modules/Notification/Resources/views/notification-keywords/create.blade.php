@extends('layouts.admin')

@section('title', (isset($keyword) ? 'Editar' : 'Adicionar') . ' Palavra-chave')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark"
            href="{{ route('admin.notificationKeywords.index') }}">Palavras-Chave de
            Notificação</a>
    </li>
    <li class="breadcrumb-item active">{{ isset($keyword) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($keyword) ? route('admin.notificationKeywords.update', $keyword->id) : route('admin.notificationKeywords.store') }}"
            method="POST">
            @csrf
            @if (isset($keyword))
                @method('PUT')
                <input hidden name="keyword_id" value="{{ $keyword->id }}" type="text">
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
                                <div class="form-group col-12">
                                    <label for="inputCode">Palavra-chave <span class="text-danger">*</span></label>
                                    <input type="text" name="keyword"
                                        value='{{ isset($keyword) ? $keyword->keyword : '' }}' class="validate form-control"
                                        required>
                                </div>
                                <div class="form-group col-12">
                                    <label for="inputCode">Descrição</label>
                                    <textarea type="text" name="description" rows="3" class="validate form-control">{{ isset($keyword) ? $keyword->description : '' }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit"
                        class="btn btn-success float-right ">{{ isset($keyword) ? 'Editar' : 'Adicionar' }}
                        Notificação</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('script')

@endsection
