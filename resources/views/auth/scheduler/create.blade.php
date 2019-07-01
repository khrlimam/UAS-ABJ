@extends('layouts.app')
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form tambah backup terjadwal</h4>
                    @if (session('fail'))
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            {!! session('fail') !!}
                        </div>
                    @endif
                    <br>
                    <form method="POST" action="{{ route('schedule.store') }}">
                        @csrf
                        <div class="form-group row">
                            <label for="name"
                                   class="col-md-3 col-form-label">{{ __('Nama') }} <span
                                        class="text-danger">*</span></label>

                            <div class="col-md-9">
                                <input id="name" type="text" placeholder="Contoh: Backup Mingguan"
                                       class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                       name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="start-date"
                                   class="col-md-3 col-form-label">{{ __('Tanggal mulai') }} <span
                                        class="text-danger">*</span></label>

                            <div class="col-md-9">
                                <input id="start-date" type="date" placeholder="jun/30/2019"
                                       class="form-control{{ $errors->has('start-date') ? ' is-invalid' : '' }}"
                                       name="start-date" value="{{ old('start-date')? old('start-date') : $startDate }}"
                                       required autofocus>

                                @if ($errors->has('start-date'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('start-date') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="start-time"
                                   class="col-md-3 col-form-label">{{ __('Jam mulai') }} <span
                                        class="text-danger">*</span></label>

                            <div class="col-md-9">
                                <input id="start-time" type="text"
                                       placeholder="15:10:00"
                                       value="{{ old('start-time')? old('start-time'): $startTime }}"
                                       class="form-control{{ $errors->has('start-time') ? ' is-invalid' : '' }}"
                                       name="start-time" required autofocus>

                                @if ($errors->has('start-time'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('start-time') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="file-name"
                                   class="col-md-3 col-form-label">{{ __('Nama file') }} <span
                                        class="text-danger">*</span></label>

                            <div class="col-md-9">
                                <input id="file-name" type="text" placeholder="Contoh: Mikrotik"
                                       class="form-control{{ $errors->has('file-name') ? ' is-invalid' : '' }}"
                                       name="file-name" value="{{ old('file-name')? old('file-name'):$hostname }}"
                                       required autofocus>

                                @if ($errors->has('file-name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('file-name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="interval-value"
                                   class="col-md-3 col-form-label">{{ __('Jalankan setiap') }} <span
                                        class="text-danger">*</span></label>

                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input id="interval-value" type="text"
                                               placeholder="Contoh: 10"
                                               class="form-control{{ $errors->has('interval-value') ? ' is-invalid' : '' }}"
                                               name="interval-value"
                                               value="{{ old('interval-value') }}"
                                               required
                                               autofocus>

                                        @if ($errors->has('interval-value'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('interval-value') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="col-md-5">
                                        <select name="interval-type" class="form-control" required>
                                            <option value="">-- Pilih satuan jarak --</option>
                                            <option {{ (old('interval-type') == 'd')? 'selected':'' }} value="d">
                                                Hari
                                            </option>
                                            <option {{ (old('interval-type') == 'h')? 'selected':'' }} value="h">
                                                Jam
                                            </option>
                                            <option {{ (old('interval-type') == 'i')? 'selected':'' }} value="i">
                                                Menit
                                            </option>
                                            <option {{ (old('interval-type') == 's')? 'selected':'' }} value="s">
                                                Detik
                                            </option>
                                        </select>

                                        @if ($errors->has('interval-type'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('interval-type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <fieldset class="form-group">
                            <div class="row">
                                <label for="status"
                                       class="col-md-3 col-form-label">{{ __('Status') }} <span
                                            class="text-danger">*</span>
                                </label>

                                <div class="col-sm-8">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="disabled" id="enable"
                                               value="no" checked>
                                        <label class="form-check-label" for="enable">
                                            Enable
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="disabled" id="disable"
                                               value="yes" {{ old('disabled') == 'yes'? 'checked':'' }}>
                                        <label class="form-check-label" for="disable">
                                            Disable
                                        </label>
                                    </div>

                                    @if ($errors->has('dhcp-status'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('pool-range-end') }}</strong>
                                    </span>
                                    @endif

                                </div>
                            </div>
                        </fieldset>

                        <div class="form-group row mb-0">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Simpan') }}
                                </button>
                                <input type="reset" value="Kosongkan" class="btn btn-warning">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
