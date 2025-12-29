@extends('layouts.admin')

@section('title', 'Editar Asset')

@section('breadcrumb')
    <li class="breadcrumb-item active"><a class="text-dark" href="{{ route('admin.assets.index') }}">Assets</a>
    </li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('css')
    <link rel="stylesheet" href="/admin-lte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
@endsection

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Editar Asset -
                            <b>{{ $asset->ticker }} </b>{{ $asset->name }}
                        </h3>
                    </div>
                    <form role="form" action="{{ route('admin.assets.update', $asset->id) }}"
                        enctype="multipart/form-data" method="post">
                        @method('PUT')
                        <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('dividendYield') ? ' has-error' : '' }}">
                                        <label for="name">
                                            Dividend Yield
                                        </label>
                                        <input type="text"
                                            class="form-control {{ $errors->has('dividendYield') ? ' is-invalid' : '' }}"
                                            id="name" name="dividendYield"
                                            value="{{ old('dividendYield') ? old('dividendYield') : (isset($asset) ? $asset->dividends_json['dividendYield'] : '') }}"
                                            placeholder="Dividend Yield">
                                        @if ($errors->has('dividendYield'))
                                            <span class="error invalid-feedback">
                                                <strong>{{ $errors->first('dividendYield') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('dividendDate') ? ' has-error' : '' }}">
                                        <label for="date">
                                            Dividend Date
                                        </label>
                                        <div class="form-group  {{ $errors->has('dividendDate') ? ' has-error' : '' }}">
                                            <div class="input-group date" data-target-input="nearest">
                                                <input type="text" name="dividendDate" autocomplete="off"
                                                    class=" form-control datetimepicker-input {{ $errors->has('dividendDate') ? ' is-invalid' : '' }}"
                                                    value="{{ old('dividendDate') ? old('dividendDate') : (isset($asset) ? $asset->dividends_json['dividendDate'] : '') }}"
                                                    data-target="#dividendDate" data-toggle="datetimepicker" />
                                                <div class="input-group-append" data-target="#dividendDate"
                                                    data-toggle="datepicker">
                                                    <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                    </div>
                                                </div>
                                                @if ($errors->has('dividendDate'))
                                                    <span class="error invalid-feedback">
                                                        <strong>{{ $errors->first('dividendDate') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="date">
                                        Ex-Dividend Date
                                    </label>
                                    <div class="form-group  {{ $errors->has('exDividendDate') ? ' has-error' : '' }}">
                                        <div class="input-group date" data-target-input="nearest">
                                            <input type="text" name="exDividendDate" autocomplete="off"
                                                class=" form-control datetimepicker-input {{ $errors->has('exDividendDate') ? ' is-invalid' : '' }}"
                                                value="{{ old('exDividendDate') ? old('exDividendDate') : (isset($asset) ? $asset->dividends_json['exDividendDate'] : '') }}"
                                                data-target="#exDividendDate" data-toggle="datetimepicker" />
                                            <div class="input-group-append" data-target="#exDividendDate"
                                                data-toggle="datepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                            @if ($errors->has('dividendDate'))
                                                <span class="error invalid-feedback">
                                                    <strong>{{ $errors->first('dividendDate') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group {{ $errors->has('exDividendDate') ? ' has-error' : '' }}">
                                        <label for="name">
                                            Categoria
                                        </label>
                                        <input type="text"
                                            class="form-control {{ $errors->has('category') ? ' is-invalid' : '' }}"
                                            id="name" name="category"
                                            value="{{ old('category') ? old('category') : (isset($asset->fundamentals_json['General']['Category']) ? $asset->fundamentals_json['General']['Category'] : '') }}"
                                            placeholder="Categoria">
                                        @if ($errors->has('category'))
                                            <span class="error invalid-feedback">
                                                <strong>{{ $errors->first('category') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group {{ $errors->has('payoutFrequency') ? ' has-error' : '' }}">
                                        <label for="name">
                                            Frequencia de Pagamento de dividendos
                                        </label>
                                        <input type="text"
                                            class="form-control {{ $errors->has('payoutFrequency') ? ' is-invalid' : '' }}"
                                            id="name" name="payoutFrequency"
                                            value="{{ old('payoutFrequency') ? old('payoutFrequency') : (isset($asset) ? $asset->dividends_json['payoutFrequency'] : '') }}"
                                            placeholder="Frequencia de Pagamento de dividendos">
                                        @if ($errors->has('payoutFrequency'))
                                            <span class="error invalid-feedback">
                                                <strong>{{ $errors->first('payoutFrequency') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($asset->type == 'EQUITY')
                                    <div class="col-12">
                                        <div class="form-group {{ $errors->has('marketCap') ? ' has-error' : '' }}">
                                            <label for="name">
                                                Market Cap
                                            </label>
                                            <input type="text"
                                                class="form-control {{ $errors->has('marketCap') ? ' is-invalid' : '' }}"
                                                id="name" name="marketCap"
                                                value="{{ old('marketCap') ? old('marketCap') : (isset($asset->fundamentals_json['marketCap']) ? $asset->fundamentals_json['marketCap'] : '') }}"
                                                placeholder="Market Cap">
                                            @if ($errors->has('marketCap'))
                                                <span class="error invalid-feedback">
                                                    <strong>{{ $errors->first('marketCap') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <div class="form-group {{ $errors->has('logo') ? ' has-error' : '' }}">
                                        <label for="logoInput">Logo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="logo"
                                                    class="custom-file-input {{ $errors->has('logo') ? ' is-invalid' : '' }}"
                                                    id="logoInput">
                                                <label class="custom-file-label" for="logoInput">Logo</label>
                                            </div>
                                        </div>
                                        @if ($asset->logo)
                                            <div class=" text-center">
                                                <img class="mt-4" style="max-width: 50%" src="{{ $asset->logo }}" />
                                            </div>
                                        @endif

                                        @if ($errors->has('logo'))
                                            <span class="error invalid-feedback">
                                                <strong>{{ $errors->first('logo') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-dark">Guardar</button>

                        </div>
                    </form>
                </div>

            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Custom Fields</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="mb-2">
                                    <b>Dividend Yield:</b>
                                    {{ isset($asset->custom_fields['dividendYield']) ? $asset->custom_fields['dividendYield'] : '' }}
                                </p>
                                <p class="mb-2">
                                    <b>Dividend Date:</b>
                                    {{ isset($asset->custom_fields['dividendDate']) ? $asset->custom_fields['dividendDate'] : '' }}
                                </p>
                                <p class="mb-2">
                                    <b>Ex-Dividend Date:</b>
                                    {{ isset($asset->custom_fields['exDividendDate']) ? $asset->custom_fields['exDividendDate'] : '' }}
                                </p>
                                <p class="mb-2">
                                    <b>Categoria:</b>
                                    {{ isset($asset->custom_fields['category']) ? $asset->custom_fields['category'] : '' }}
                                </p>
                                <p class="mb-2">
                                    <b>Frequencia de Pagamento de dividendos:</b>
                                    {{ isset($asset->custom_fields['payoutFrequency']) ? $asset->custom_fields['payoutFrequency'] : '' }}
                                </p>
                                @if ($asset->type == 'EQUITY')
                                    <p class="mb-2">
                                        <b>Market Cap:</b>
                                        {{ isset($asset->custom_fields['marketCap']) ? $asset->custom_fields['marketCap'] : '' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
