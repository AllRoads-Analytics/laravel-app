@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>Org: {{ $Tracker->Organization->name ?? 'n/a' }}</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Site</th>
                    </tr>
                </thead>

                <tbody>
                    @if ( ! $hosts->current())
                        <tr><td><i>No tracking data sent yet.</i></td></tr>
                    @endif

                    @foreach ($hosts as $host)
                        <tr>
                            <td>
                                <a href="{{ route('pathfinder.tracker.host', [
                                    'tracker' => $Tracker,
                                    'host' => $host['host'],
                                ]) }}">
                                    {{ $host['host'] }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
