@extends('layouts.app_admin_v2')

@section('page', 'Emails' . $emailType->name)

@section('css')
    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/tagify.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/tagify-data.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/quill.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/select2-theme.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('duralux-admin/assets/vendors/css/datepicker.min.css') }}">
    <!--! END: Vendors CSS-->
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item">Definições</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.emailLayouts.index') }}">Layouts de Email</a></li>
    @if (isset($emailLayout))
        <li class="breadcrumb-item active">{{ $emailType->name }}</li>
    @endif
@endsection

@section('header-right')
    <div class="page-header-right-items">
        <div class="d-flex d-md-none">
            <a href="javascript:void(0)" class="page-header-right-close-toggle">
                <i class="feather-arrow-left me-2"></i>
                <span>Back</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
            <button type="submit" form="emailTypeForm" class="btn btn-primary " data-alert-target="#alertMessage">
                <i class="feather-save me-2"></i>
                <span>Guardar</span>
            </button>
        </div>
    </div>
    <div class="d-md-none d-flex align-items-center">
        <a href="javascript:void(0)" class="page-header-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0">
        <div class="d-md-none d-flex align-items-center justify-content-between">
            <a href="javascript:void(0)" class="page-content-left-open-toggle">
                <i class="feather-align-left fs-20"></i>
            </a>
            <a href="javascript:void(0)" class="page-content-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <div class="nav-tabs-wrapper page-content-left-sidebar-wrapper">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-content-left-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <ul class="nav nav-tabs nav-tabs-custom-style" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#emailTab">Email</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tagsTab">Tags</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="main-content">
        <div class="tab-content">
            <div class="tab-pane fade active show" id="emailTab">
                <form action="{{ route('v2.settings.emailLayouts.update', $emailType->id) }}" method="POST"
                    id="emailTypeForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12">
                            <div class="card stretch stretch-full">
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label class="form-label">Assunto <span class="text-danger">*</span></label>
                                        <input type="text" name="subject" class="form-control" placeholder="Assunto"
                                            value="{{ isset($emailLayout) ? $emailLayout->subject : old('subject') }}"
                                            required>
                                        <div class="error-container"></div>
                                    </div>


                                    <div class="mb-4">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <div id="emailQuill" class="ht-200">
                                        </div>
                                        <input type="hidden" name="email" id="email">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Assinatura <span class="text-danger">*</span></label>
                                        <div id="signatureQuill" class="ht-200">
                                        </div>
                                        <input type="hidden" name="signature" id="signature">
                                    </div>

                                    <div class="mb-4">
                                        <label for="choose-file" class="custom-file-upload" id="choose-file-label">
                                            Anexo
                                        </label>
                                        <input name="attach" type="file" accept="application/pdf" id="choose-file"
                                            style="display: none">
                                        <div class="error-container"></div>
                                        @if (isset($emailLayout) && $emailLayout->attach)
                                            <iframe class="mt-3" src="{{ asset($emailLayout->attach) }}"
                                                width="50%" style="border: none;">
                                            </iframe>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="tagsTab">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card stretch stretch-full">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Keywords</th>
                                                <th>Descrição</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($emailType->keywords as $keyword)
                                                <tr>
                                                    <td>{{ $keyword->keyword }}</td>
                                                    <td>{{ $keyword->description }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('duralux-admin/assets/vendors/js/tagify.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/tagify-data.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/quill.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/select2-active.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('duralux-admin/assets/vendors/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-validation/messages_pt_PT.js') }}"></script>
    <!--! END: Vendors JS !-->

    <script>
        "use strict";


        $("#emailTypeForm").validate({
                errorPlacement: function(error, element) {
                    element.closest('.mb-4')
                        .find('.error-container')
                        .html(error);
                },
                errorClass: 'text-danger small',
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            }),
            $(document).ready(function() {

                const quill = new Quill("#emailQuill", {
                    placeholder: "Email...",
                    theme: "snow",
                });
                const quillSignature = new Quill("#signatureQuill", {
                    placeholder: "Assinatura...",
                    theme: "snow",
                });

                quill.setContents(quill.clipboard.convert(`{!! isset($emailLayout) ? $emailLayout->email : old('email') !!}`));
                quillSignature.setContents(quillSignature.clipboard.convert(`{!! isset($emailLayout) ? $emailLayout->signature : old('signature') !!}`));

                $('#emailTypeForm').on('submit', function(e) {
                    e.preventDefault();

                    const fileInput = $('#choose-file')[0];

                    if (!$('#emailTypeForm').valid()) {
                        return;
                    }

                    document.getElementById("email").value = quill.root.innerHTML;
                    document.getElementById("signature").value = quillSignature.root.innerHTML;

                    const t = Swal.mixin({
                        customClass: {
                            confirmButton: "btn btn-success m-1",
                            cancelButton: "btn btn-danger m-1",
                        },
                        buttonsStyling: !1,
                    });
                    $.ajax({
                        url: $('#emailTypeForm').attr('action'),
                        method: $('#emailTypeForm').attr('method'),
                        data: new FormData($('#emailTypeForm')[0]),
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            t.fire("Editada!", "Layout de email editado com sucesso.", "success");
                        },
                        error: function(res) {
                            console.log(res);
                            t.fire("Erro", 'Não foi possível adicionar layout de email.', "error");
                        }
                    });
                });
            }),
            $(document).ready(function() {
                $("#choose-file").change(function() {
                    $(this).prev("label").clone();
                    var e = $("#choose-file")[0].files[0].name;
                    $(this).prev("label").text(e)
                })
            });
    </script>


@endsection
