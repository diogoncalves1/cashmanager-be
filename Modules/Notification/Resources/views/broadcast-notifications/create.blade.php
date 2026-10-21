@extends('layouts.admin')

@section('title', (isset($notification) ? 'Editar' : 'Adicionar') . ' Notificação')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.notifications.index') }}">Notificações</a>
    </li>
    <li class="breadcrumb-item active">{{ isset($notification) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('/admin-lte/plugins/summernote/summernote-bs4.min.css') }}">
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($notification) ? route('admin.notifications.update', $notification->id) : route('admin.notifications.store') }}"
            method="POST">
            @csrf
            @if (isset($notification))
                @method('PUT')
                <input hidden name="notification_id" value="{{ $notification->id }}" type="text">
                <input hidden name="notification_type_code" value="{{ $notification->notificationType->code }}"
                    type="text">
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
                                @if (!isset($notification))
                                    <div class="form-group col-6 {{ $errors->has('code') ? 'has-error' : '' }}">
                                        <label for="inputCode">Código <span class="text-danger">*</span></label>
                                        <input type="text" name="code"
                                            value='{{ isset($notification) ? $notification->notificationType->code : old('code') }}'
                                            class="validate form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                            required>
                                        @error('code')
                                            <span class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                @endif
                                <div
                                    class="form-group {{ isset($notification) ? 'col-12' : 'col-6' }} {{ $errors->has('pathname') ? 'has-error' : '' }}">
                                    <label for="inputPathname">Pathname <span class="text-danger">*</span></label>
                                    <input type="text" name="pathname"
                                        value='{{ isset($notification) ? $notification->notificationType->pathname : old('pathname') }}'
                                        class="validate form-control {{ $errors->has('pathname') ? 'is-invalid' : '' }}"
                                        required>
                                    @error('pathname')
                                        <span class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                @if (!isset($notification))
                                    <div class="col-2">
                                        <div class="form-group clearfix">
                                            <div class="icheck-primary d-inline ">
                                                <input class="form-control" type="checkbox" id="send-email"
                                                    name="send_email" value="1"
                                                    {{ isset($notification) ? ($notification->send_email ? 'checked' : '') : (old('send_email') ? 'checked' : '') }}>
                                                <label for="send-email">Enviar email</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

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
                                        <div
                                            class="form-group {{ $errors->has('title.' . $language->code) ? 'has-error' : '' }}">
                                            <label for="inputDisplayName">Título em {{ $language->name->pt }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="title[{{ $language->code }}]"
                                                value="{{ isset($notification->notificationType->title) ? $notification->notificationType->title[$language->code] : old('title.' . $language->code) }}"
                                                class="validate form-control {{ $errors->has('title.' . $language->code) ? 'is-invalid' : '' }}">
                                            @error('title.' . $language->code)
                                                <span
                                                    class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        <div
                                            class="form-group {{ $errors->has('message.' . $language->code) ? 'has-error' : '' }}">
                                            <label for="inputDisplayName">Mensagem em {{ $language->name->pt }} <span
                                                    class="text-danger">*</span></label>
                                            <textarea type="text" name="message[{{ $language->code }}]"
                                                class="validate form-control {{ $errors->has('message.' . $language->code) ? 'is-invalid' : '' }}">{{ isset($notification->notificationType->message) ? $notification->notificationType->message[$language->code] : old('message.' . $language->code) }}</textarea>
                                            @error('message.' . $language->code)
                                                <span
                                                    class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>

                                        @if (!isset($notification))
                                            <div
                                                class="mail-message {{ isset($notification) ? ($notification->send_email ? '' : 'd-none') : (old('send_email') ? '' : 'd-none') }} row">
                                                <div class="border mb-2 col-12"></div>
                                                <h4>Email</h4>
                                                <div class="col-md-12">
                                                    <div
                                                        class="form-group {{ $errors->has('mail_subject.' . $language->code) ? ' has-error' : '' }}">
                                                        <label for="subject">
                                                            <span class="text-danger">*</span>
                                                            Assunto no Email em {{ $language->name->pt }}
                                                        </label>
                                                        <input type="text" name="mail_subject[{{ $language->code }}]"
                                                            value="{{ isset($notification->notificationType->mail_subject) ? $notification->notificationType->mail_subject[$language->code] ?? old('mail_subject.' . $language->code) : old('mail_subject.' . $language->code) }}"
                                                            class="validate form-control {{ $errors->has('mail_subject.' . $language->code) ? 'is-invalid' : '' }}">
                                                        @error('mail_subject.' . $language->code)
                                                            <span
                                                                class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div
                                                        class="form-group {{ $errors->has('mail_message.' . $language->code) ? ' has-error' : '' }}">
                                                        <label for="email">
                                                            <span class="text-danger">*</span>
                                                            Mensagem no Email em {{ $language->name->pt }}
                                                        </label>
                                                        <textarea class="form-control summernote {{ $errors->has('mail_message.' . $language->code) ? 'is-invalid' : '' }}"
                                                            rows="6" name="mail_message[{{ $language->code }}]" placeholder="Mensagem">{{ old('mail_message.' . $language->code) ?: (isset($notification) ? $notification->notificationType->mail_message[$language->code] : '') }}</textarea>
                                                        @error('mail_message.' . $language->code)
                                                            <span
                                                                class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div
                                                        class="form-group {{ $errors->has('mail_signature.' . $language->code) ? ' has-error' : '' }}">
                                                        <label for="signature">
                                                            <span class="text-danger">*</span>
                                                            Assinatura no Email em {{ $language->name->pt }}
                                                        </label>
                                                        <textarea class="form-control summernote {{ $errors->has('mail_signature.' . $language->code) ? 'is-invalid' : '' }}"
                                                            rows="6" name="mail_signature[{{ $language->code }}]" placeholder="Assinatura">{{ old('mail_signature.' . $language->code) ?: (isset($notification) ? $notification->notificationType->mail_signature[$language->code] : '') }}</textarea>
                                                        @error('mail_signature.' . $language->code)
                                                            <span
                                                                class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit"
                        class="btn btn-success float-right ">{{ isset($notification) ? 'Editar' : 'Adicionar' }}
                        Notificação</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('script')

    <script>
        $(function() {
            const sendEmailCheckbox = $("#send-email");

            $("#send-email").on('change', function(e) {

                if ($("#send-email").prop("checked")) {
                    $(".mail-message").removeClass("d-none");

                } else {
                    $(".mail-message").addClass("d-none");
                    $('.summernote').summernote('reset');
                }
            })
        })
    </script>


    <script src={{ asset('/vendor/ckeditor/ckeditor.js') }}></script>
    <script src="{{ asset('/admin-lte/plugins/summernote/summernote-bs4.min.js') }}"></script>

    <script>
        $(function() {
            $('.summernote').summernote()
        });
    </script>

@endsection
