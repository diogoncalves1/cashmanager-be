@extends('layouts.app_admin')

@section('css')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item">Definições</li>
    <li class="breadcrumb-item"><a href="{{route('admin.settings.emailLayouts.index')}}">Layouts de Email</a></li>
    @if(isset($emailLayout))
        <li class="breadcrumb-item active">{{$emailType->name}}</li>
    @endif
@endsection
@section('content')
    @include('top.messages')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    @if(isset($emailType))
                        <h3 class="card-title">Email "{{$emailType->name}}"</h3>
                    @endif
                </div>
                <div class="col-md-12 ">
                    <form role="form" action="{{ route('admin.settings.emailLayouts.update', [$emailType->id])}}"
                          enctype="multipart/form-data" method="post">
                        @csrf
                        @method('put')
                        <div class="card card-tabs">
                            <div class="card-header p-0 pt-1">
                                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                           href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                           aria-selected="true">Email</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link " id="custom-tabs-one-profile-tab" data-toggle="pill"
                                           href="#custom-tabs-one-profile" role="tab"
                                           aria-controls="custom-tabs-one-profile" aria-selected="false">Tags</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-one-tabContent">
                                    <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                         aria-labelledby="custom-tabs-one-home-tab">
                                        <div class="col-md-12">
                                            <div class="form-group {{ $errors->has('subject') ? ' has-error' : '' }}">
                                                <label for="subject">
                                                    <span class="text-danger">*</span> Assunto
                                                </label>
                                                <input type="text" class="form-control" id="subject"
                                                       name="subject"
                                                       value="{{old('subject') ? : (isset($emailLayout) ? $emailLayout->subject : '')}}"
                                                       placeholder="Assunto">
                                                @if ($errors->has('subject'))
                                                    <span class="help-block">
                                                <strong>{{ $errors->first('subject') }}</strong>
                                            </span>
                                                @endif
                                            </div>


                                        </div>
                                        <div class="col-md-12">

                                            <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                                                <label for="email">
                                                    <span class="text-danger">*</span>
                                                    Email
                                                </label>
                                                <textarea class="form-control" rows="6" name="email"
                                                          placeholder="Email"
                                                          id="email"
                                                >{{old('email') ? : (isset($emailLayout) ? $emailLayout->email : '')}}</textarea>
                                                @if ($errors->has('email'))
                                                    <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-12">

                                            <div class="form-group {{ $errors->has('signature') ? ' has-error' : '' }}">
                                                <label for="email">
                                                    <span class="text-danger">*</span>
                                                    Assinatura
                                                </label>
                                                <textarea class="form-control" name="signature" id="signature"
                                                          placeholder="Assinatura"
                                                >{{old('signature') ? : (isset($emailLayout) ? $emailLayout->signature : '')}}</textarea>
                                                @if ($errors->has('signature'))
                                                    <span class="help-block">
                                            <strong>{{ $errors->first('signature') }}</strong>
                                        </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-12 ">
                                            <div class="form-group">
                                                <label for="attach-name">Anexo</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-file-pdf"></i>
                                                </span>
                                                    </div>
                                                    <input type="text" class="form-control image-input" id="attach-name"
                                                           value="{{isset($emailLayout) ? $emailLayout->attach_name : ''}}"
                                                           name="attach-name" readonly>
                                                    <span class="input-group-btn">
                                <span class="btn btn-default btn-file"><span><i class="fa fa-folder-open mr-2">
                                        </i>Adicionar anexo</span>
                                    <input type="file" id="attach" name="attach"
                                           accept="application/pdf"/></span>
                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel"
                                         aria-labelledby="custom-tabs-one-profile-tab">
                                        <table class="table table">
                                            <thead>
                                            <tr>
                                                <th>Keywords</th>
                                                <th>Descrição</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($emailType->keywords as $keyword)
                                                <tr>
                                                    <td>{{$keyword->keyword}}</td>
                                                    <td>{{$keyword->description}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-dark">Guardar</button>
                            </div>
                            <!-- /.card -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')

    <script src={{asset("/vendor/ckeditor/ckeditor.js")}}></script>

    <script>
        $(function () {
            CKEDITOR.replace('email', {
                customConfig: '{{asset('/vendor/ckeditor/custom/config.js')}}'
            });
            CKEDITOR.replace('signature', {
                customConfig: '{{asset('/vendor/ckeditor/custom/config.js')}}'
            });
            $('#attach-name').click(function (e) {
                $('#attach').trigger('click');
            });
            $('#attach').change(function (e) {
                if ($(this)[0].files !== undefined) {
                    var files = $(this)[0].files;
                    var name = '';
                    $.each(files, function (index, value) {
                        name += value.name + ', ';
                    });
                    $('#attach-name').val(name.slice(0, -2));
                } else // Internet Explorer 9 Compatibility
                {
                    var name = $(this).val().split(/[\\/]/);
                    $('#attach-name').val(name[name.length - 1]);
                }

            });
        });
    </script>

@endsection
