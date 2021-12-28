@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <p class="m-0 text-secondary">
                {{ $Tracker->Organization->name }}
            </p>

            <h2>Funnels</h2>

            @foreach ($funnels as $Funnel)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <a href="{{ route('pathfinder.tracker.host', [
                                    'tracker' => $Tracker->pixel_id,
                                    'host' => $Funnel->hostname,
                                    'funnel' => $Funnel->id,
                                ]) }}">
                                    <span class="fs-5">{{ $Funnel->name }}</span>
                                </a>
                            </div>

                            <div class="col">
                                Site:
                                {{ $Funnel->hostname }}
                            </div>

                            <div x-data="{ show: false}"
                            class="col text-end">
                                <div>
                                    <button x-on:click="show = !show" class="btn btn-light">
                                        Pages
                                        <span x-show="!show" x-cloak><i class="fas fa-chevron-down"></i></span>
                                        <span x-show="show"><i class="fas fa-chevron-up"></i></span>
                                    </button>
                                </div>
                                <div x-show="show" x-cloak class="mt-2 text-secondary">
                                    {!! implode('<br>', $Funnel->getPages()) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('pathfinder.tracker', $Tracker->pixel_id) }}" class="btn btn-success mt-2">
                <i class="fas fa-plus"></i>
                New Funnel
            </a>
        </div>
    </div>
</div>
@endsection
