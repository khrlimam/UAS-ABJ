@extends('layouts.app')

@section('content')

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('fail'))
        <div class="alert alert-danger" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {!! session('fail') !!}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <h3 class="card-title">Daftar file konfigurasi</h3>
        </div>
        <table class="table">
            <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>File</th>
                <th>Ukuran</th>
                <th>Tanggal dibuat</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($files as $file)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><i class="fa fa-file"></i> {{ $file['name'] }}</td>
                    <td>{{ $file['size'] }}</td>
                    <td>{{ $file['creation-time'] }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                            <a data-toggle="tooltip" data-original-title="Lihat" class="btn btn-success"
                               href="{{ route('backup.show', $file['name']) }}"><i
                                        class="fa fa-eye"></i></a>
                            <a data-toggle="tooltip" data-original-title="Download"
                               href="{{ route('backup.download', $file['name']) }}" class="btn btn-primary"><i
                                        class="fa fa-download"></i></a>
                            <a href="{{ route('backup.load', $file['name']) }}" data-toggle="tooltip"
                               data-original-title="Restore"
                               class="btn btn-secondary"><i class="fa fa-undo"></i>
                            </a>
                            <button data-toggle="tooltip" data-original-title="Hapus" class="btn btn-danger"
                                    onclick='confirmDelete("{{ route('backup.delete', $file['.id']) }}")'><i
                                        class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            })
        });

        function confirmDelete(url) {
            swal("Apakah anda yakin ingin menghapus file?", {
                buttons: {
                    cancel: "Kembali",
                    yes: {
                        text: "Ya",
                        value: "yes",
                    },
                },
            })
                .then((value) => {
                    if (value === 'yes') window.location = url;
                });
        }

    </script>
@endsection