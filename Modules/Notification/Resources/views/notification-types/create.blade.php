@extends('layouts.admin')

@section('title', (isset($type) ? 'Editar' : 'Adicionar') . ' Notificação')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.notificationTypes.index') }}">Tipos de
            Notificações</a>
    </li>
    <li class="breadcrumb-item active">{{ isset($type) ? 'Editar' : 'Adicionar' }}</li>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('/admin-lte/plugins/summernote/summernote-bs4.min.css') }}">
@endsection

@section('content')
    <section class="content">
        <form
            action="{{ isset($type) ? route('admin.notificationTypes.update', $type->id) : route('admin.notificationTypes.store') }}"
            method="POST">
            @csrf
            @if (isset($type))
                @method('PUT')
                <input hidden name="type_id" value="{{ $type->id }}" type="text">
            @else
                @method('POST')
            @endif

            <ul class="nav nav-tabs mb-2">
                <li class="nav-item"><a class="nav-link active" href="#main-input" data-toggle="tab">Conteúdo</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="#keywords-input" data-toggle="tab">Keywords</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="main-input">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Geral</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-6 {{ $errors->has('code') ? 'has-error' : '' }}">
                                            <label for="inputCode">Código <span class="text-danger">*</span></label>
                                            <input type="text" name="code"
                                                value='{{ isset($type) ? $type->code : old('code') }}'
                                                class="validate form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                                                required>
                                            @error('code')
                                                <span class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-6 {{ $errors->has('pathname') ? 'has-error' : '' }}">
                                            <label for="inputPathname">Pathname <span class="text-danger">*</span></label>
                                            <input type="text" name="pathname"
                                                value='{{ isset($type) ? $type->pathname : old('pathname') }}'
                                                class="validate form-control {{ $errors->has('pathname') ? 'is-invalid' : '' }}"
                                                required>
                                            @error('pathname')
                                                <span
                                                    class="error invalid-feedback"><strong>{{ $message }}</strong></span>
                                            @enderror
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
                                            <div class="tab-pane {{ $key == 0 ? 'active' : '' }}"
                                                id="{{ $language->code }}">
                                                <div class="form-group">
                                                    <label for="inputDisplayName">Título em {{ $language->name->pt }} <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" name="title[{{ $language->code }}]"
                                                        value="{{ isset($type->title) ? $type->title[$language->code] ?? '' : '' }}"
                                                        class="validate form-control">
                                                    <span class="error invalid-feedback">Preencha este
                                                        campo</span>
                                                    <span class="success valid-feedback">Campo preenchido</span>
                                                </div>

                                                <div class="form-group">
                                                    <label for="inputDisplayName">Mensagem em {{ $language->name->pt }}
                                                        <span class="text-danger">*</span></label>
                                                    <textarea type="text" name="message[{{ $language->code }}]" class="validate form-control">{{ isset($type->message) ? $type->message[$language->code] ?? '' : '' }}</textarea>
                                                    <span class="error invalid-feedback">Preencha este
                                                        campo</span>
                                                    <span class="success valid-feedback">Campo preenchido</span>
                                                </div>


                                                <div class="mail-message row">
                                                    <div class="border mb-2 col-12"></div>
                                                    <h4>Email</h4>
                                                    <div class="col-md-12">
                                                        <div
                                                            class="form-group {{ $errors->has('mail_subject.' . $language->code) ? ' has-error' : '' }}">
                                                            <label for="subject">
                                                                <span class="text-danger">*</span>
                                                                Assunto no Email em {{ $language->name->pt }}
                                                            </label>
                                                            <textarea class="form-control summernote" rows="3" name="mail_subject[{{ $language->code }}]"
                                                                placeholder="Assunto">{{ old('mail_subject.' . $language->code) ?: (isset($type) ? $type->mail_subject[$language->code] : '') }}</textarea>
                                                            @if ($errors->has('mail_subject.' . $language->code))
                                                                <span class="help-block">
                                                                    <strong>{{ $errors->first('mail_subject.' . $language->code) }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div
                                                            class="form-group {{ $errors->has('mail_message.' . $language->code) ? ' has-error' : '' }}">
                                                            <label for="email">
                                                                <span class="text-danger">*</span>
                                                                Mensagem no Email em {{ $language->name->pt }}
                                                            </label>
                                                            <textarea class="form-control summernote" rows="6" name="mail_message[{{ $language->code }}]"
                                                                placeholder="Mensagem">{{ (old('mail_message.' . $language->code) ?: isset($type)) ? $type->mail_message[$language->code] : '' }}</textarea>
                                                            @if ($errors->has('mail_message.' . $language->code))
                                                                <span class="help-block">
                                                                    <strong>{{ $errors->first('mail_message.' . $language->code) }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div
                                                            class="form-group {{ $errors->has('mail_signature.' . $language->code) ? ' has-error' : '' }}">
                                                            <label for="signature">
                                                                <span class="text-danger">*</span>
                                                                Assinatura no Email em {{ $language->name->pt }}
                                                            </label>
                                                            <textarea class="form-control summernote" rows="6" name="mail_signature[{{ $language->code }}]"
                                                                placeholder="Assinatura">{{ old('mail_signature.' . $language->code) ?: (isset($type) ? $type->mail_signature[$language->code] : '') }}</textarea>
                                                            @if ($errors->has('mail_signature.' . $language->code))
                                                                <span class="help-block">
                                                                    <strong>{{ $errors->first('mail_signature.' . $language->code) }}</strong>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="tab-pane" id="keywords-input">
                    <div class="col-12">

                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Keywords</h3>

                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @foreach ($keywordsList as $index => $keyword)
                                    @if ($index % 4 == 0)
                                        <div class="row">
                                    @endif
                                    <div class="col-3">
                                        <div class="form-group clearfix">
                                            <div class="icheck-danger d-inline ">
                                                <input class="form-control" id="keyword-{{ $keyword->id }}"
                                                    type="checkbox" name="keywords[]" value="{{ $keyword->id }}"
                                                    {{ isset($typeKeywordIds) && in_array($keyword->id, $typeKeywordIds) ? 'checked' : '' }}>
                                                <label for="keyword-{{ $keyword->id }}">{{ $keyword->keyword }}</label>
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
            </div>

            <div class="row">
                <div class="col-12">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit"
                        class="btn btn-success float-right ">{{ isset($type) ? 'Editar' : 'Adicionar' }}
                        Tipo de Notificação</button>
                </div>
            </div>

        </form>
    </section>
@endsection

@section('script')
    <script src={{ asset('/vendor/ckeditor/ckeditor.js') }}></script>

    <script src="{{ asset('/admin-lte/plugins/summernote/summernote-bs4.min.js') }}"></script>

    <script>
        $(function() {

            const keywords = @json($keywordsList->pluck('keyword'));

            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],

                    ['font', [
                        'bold',
                        'italic',
                        'underline',
                        'strikethrough',
                        'superscript',
                        'subscript',
                        'clear'
                    ]],

                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['fontsizeunit', ['fontsizeunit']],
                    ['color', ['color']],

                    ['para', [
                        'ul',
                        'ol',
                        'paragraph',
                        'height'
                    ]],

                    ['table', ['table']],

                    ['insert', [
                        'link',
                        'picture',
                        'video',
                        'hr'
                    ]],

                    ['view', [
                        'fullscreen',
                        'codeview',
                        'help'
                    ]],

                    ['history', [
                        'undo',
                        'redo'
                    ]],

                    ['custom', ['keywords']]
                ],
                buttons: {
                    keywords: function(context) {

                        const ui = $.summernote.ui;

                        let list = '';

                        keywords.forEach(keyword => {
                            list += `
                            <a href="#" class="dropdown-item insert-keyword"
                               data-value="${keyword}">
                               ${keyword}
                            </a>
                        `;
                        });

                        const button = ui.buttonGroup([
                            ui.button({
                                className: 'dropdown-toggle',
                                contents: 'Keywords <span class="caret"></span>',
                                tooltip: 'Inserir Keyword',
                                data: {
                                    toggle: 'dropdown'
                                }
                            }),

                            ui.dropdown({
                                className: 'dropdown-style',
                                contents: list,
                                callback: function($dropdown) {

                                    $dropdown.find('.insert-keyword').click(function(
                                        e) {
                                        e.preventDefault();

                                        const value = $(this).data('value');

                                        context.invoke('editor.insertText',
                                            value);
                                    });
                                }
                            })
                        ]);

                        return button.render();
                    }
                },

                hint: {
                    match: /\[\$(\w*)$/,
                    search: function(keyword, callback) {
                        callback($.grep(keywords, function(item) {
                            return item.toLowerCase().indexOf('[$' + keyword
                                .toLowerCase()) === 0;
                        }));
                    },
                    content: function(item) {
                        return item;
                    }
                }
            });

        });
    </script>

@endsection
