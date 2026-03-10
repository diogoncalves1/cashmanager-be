@extends('layouts.app_admin')

@section('css')
    <link rel="stylesheet" href={{asset("admin-lte/plugins/select2/css/select2.min.css")}}>

@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item">Tipos de Email</li>
    <li class="breadcrumb-item active">{{!isset($emailType) ? 'Adicionar ':'Editar '}}Tipo de Email
    </li>
@endsection
@section('content')
    @include('top.messages')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{!isset($emailType) ? 'Adicionar ':'Editar '}}Tipo de Email</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                @if(!isset($emailType))
                    <form role="form" action="{{ route('admin.emailTypes.store')}}" method="post">
                        @else
                            <form role="form"
                                  action="{{ route('admin.emailTypes.update', [$emailType->id])}}"
                                  method="post">
                                <input type="hidden" name="email_type_id" value="{{$emailType->id}}">

                                @method('PUT')
                                @endif
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">

                                                <label for="name">
                                                    <span class="text-danger">*</span>
                                                    Nome
                                                </label>
                                                <input type="text"
                                                       class="form-control  {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                                       id="name" name="name"
                                                       value="{{old('name') ? old('name') : (isset($emailType) ? $emailType->name : '')}}"
                                                       placeholder="Nome">
                                                @if ($errors->has('name'))
                                                    <span
                                                        class="error invalid-feedback">{{ $errors->first('name') }}</span>
                                                @endif

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="code">
                                                    <span class="text-danger">*</span>
                                                    Código
                                                </label>
                                                <input type="text"
                                                       class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}"
                                                       id="code" name="code"
                                                       value="{{old('code') ?  : (isset($emailType) ? $emailType->code : '')}}"
                                                       placeholder="Código">
                                                @if ($errors->has('code'))
                                                    <span
                                                        class="error invalid-feedback">{{ $errors->first('code') }}</span>
                                                @endif

                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>
                                                    Keywords
                                                </label>
                                                <div class="input-group" style="width: 100%;">
                                                    <select class="form-control select2"
                                                            multiple="multiple"
                                                            style="width: 100%;"
                                                            id="keywords-select"
                                                            name="keywords[]">
                                                        @if(isset($emailType))
                                                            @foreach($emailType->keywords as $keyword)
                                                                <option selected>{{$keyword->keyword}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group  {{ $errors->has('descriptions') ? ' has-error' : '' }}">
                                                <label>
                                                    Descrições
                                                </label>
                                                <div class="input-group" style="width: 100%;">
                                                    <select class="form-control select2"
                                                            multiple="multiple"
                                                            style="width: 100%;"
                                                            id="descriptions-select"
                                                            name="descriptions[]">
                                                        @if(isset($emailType))
                                                            @foreach($emailType->keywords as $keyword)
                                                                <option selected>{{$keyword->description}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                @if ($errors->has('descriptions'))
                                                    <span class="help-block">
                                            <strong>{{ $errors->first('descriptions') }}</strong>
                                        </span>
                                                @endif
                                            </div>
                                        </div></div>
                                </div>                                <!-- /.box-body -->
                                <div class="card-footer">
                                    <button type="submit" class="btn bg-navy margin">
                                        {{!isset($emailType) ? 'Adicionar' : 'Editar'}}
                                    </button>
                                </div>
                            </form>
                    </form>
            </div>
        </div>
    </div>
@endsection


@section('script')

    <script src={{asset("/admin-lte/plugins/select2/js/select2.full.min.js")}}></script>

    <script>
        $(function () {

            $("#keywords-select").select2({
                language: "pt",
                placeholder: "Acrescente Keywords",
                allowClear: true,
                tags: true,
                tokenSeparators: [',']
            });

            $("#descriptions-select").select2({
                language: "pt",
                placeholder: "Acrescente descrições para as keywords",
                allowClear: true,
                tags: true,
                tokenSeparators: [',']
            });
        });
    </script>

@endsection
