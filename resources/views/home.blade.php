@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if ($organizations->count())
                <h3>Your Organizations</h3>
            @endif

            @foreach ($organizations as $Organization)

                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-5 fs-4">
                                {{ $Organization->name }}
                            </div>

                            <div class="col-6 text-center">
                                <a href="{{ $Organization->getTracker()->getRoute() }}"
                                class="btn btn-primary me-2">
                                    <i class="fas fa-compass"></i>
                                    Pathfinder
                                </a>

                                <a href="{{ route('funnels.index', ['organization' => $Organization->id]) }}"
                                class="btn btn-primary">
                                    <i class="fas fa-filter"></i>
                                    Saved Funnels
                                </a>
                            </div>

                            <div class="col-1 text-end">
                                <a href="{{ route('organizations.show', $Organization->id) }}"
                                class="btn btn-secondary btn-sm">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('organizations.create') }}" class="btn btn-success mt-2">
                <i class="fas fa-plus"></i>
                New Organization
            </a>
        </div>
    </div>
</div>
@endsection
