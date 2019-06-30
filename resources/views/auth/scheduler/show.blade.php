@extends('layouts.app')

@section('css')

    <style>

        pre {
            font-family: monospace;
            padding: 0.5em;
            line-height: 0;
            font-size: 11pt;
            counter-reset: line;
        }

        pre span {
            display: block;
            line-height: 1.5rem;
        }

        pre span:before {
            counter-increment: line;
            content: counter(line);
            display: inline-block;
            border-right: 1px solid #ddd;
            padding: 0 .9em;
            margin-right: .5em;
            color: #888;
        }

    </style>
@endsection

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
            <div class="btn-group float-right" role="group" aria-label="Basic example">
                <a href="{{ route('backup.download', request()->route('backup')) }}" class="btn btn-outline-primary"><i
                            class="fa fa-download"></i> Download</a>
                <a class="btn btn-outline-secondary" href="{{ route('backup.load', request()->route('backup')) }}"><i
                            class="fa fa-undo"></i> Restore</a>
            </div>
            <h3 class="card-title"><i class="fa fa-file"></i> {{ request()->route('backup') }}</h3>
            <pre>
                @foreach($contents as $content)
                    <span>{{ $content }}</span>
                @endforeach
            </pre>
        </div>
    </div>

@endsection