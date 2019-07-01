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
            <a class="btn btn-outline-primary float-right" href="{{ route('schedule.create') }}">Tambah Backup
                Terjadwal</a>
            <h3 class="card-title">Daftar backup terjadwal</h3>
        </div>
        <table class="table">
            <thead class="thead-dark">
            <tr>
                <th></th>
                <th>Nama</th>
                <th>Mulai</th>
                <th>Jarak</th>
                <th>Tereksekusi</th>
                <th>Berikutnya</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($schedules as $schedule)
                <tr>
                    <td>
                        @if($schedule['disabled'] == 'true')
                            <span data-toggle="tooltip" data-original-title="Disabled"
                                  class="badge badge-pill badge-danger"><i class="fa fa-times"></i></span>
                        @endif
                    </td>
                    <td>{{ $schedule['name'] }}</td>
                    <td>{{ $schedule['start-time'] }}</td>
                    <td>{{ $schedule['interval'] }}</td>
                    <td>{{ $schedule['run-count'] }} kali</td>
                    <td>{{ key_exists('next-run', $schedule)? $schedule['next-run']:'-' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                            @if ($schedule['disabled'] == 'true')
                                <a data-toggle="tooltip" title="Toggle"
                                   href="{{ route('schedule.toggle', ['id' => $schedule['.id'], 'toggle' => 'no']) }}"
                                   class="btn btn-warning">Enable</a>
                            @else
                                <a data-toggle="tooltip" title="Toggle"
                                   href="{{ route('schedule.toggle', ['id' => $schedule['.id'], 'toggle' => 'yes']) }}"
                                   class="btn btn-warning">Disable</a>
                            @endif
                            <a data-toggle="tooltip" title="Edit data"
                               href="{{ route('schedule.edit', $schedule['.id']) }}"
                               class="btn btn-dark"><i class="fa fa-edit"
                                                       aria-hidden="true"></i></a>
                            <button onclick="confirmDelete('{{ route('schedule.destroy', $schedule['.id']) }}')"
                                    data-toggle="tooltip"
                                    title="Hapus data"
                                    class="btn btn-danger"><i class="fa fa-trash"
                                                              aria-hidden="true"></i></button>
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
            swal("Apakah anda yakin ingin menghapus scheduler?", {
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