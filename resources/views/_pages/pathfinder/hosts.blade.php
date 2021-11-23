@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Sites</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($hosts as $host)
                        <tr>
                            <td>
                                <a href="{{ route('pathfinder.tracker.host', [
                                    'tracker_pixel_id' => $Tracker->pixel_id,
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
