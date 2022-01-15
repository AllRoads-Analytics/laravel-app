@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            @if ($organizations->count())
                <h1 class="fs-3 m-0">
                    Your Trackers
                </h1>

                <div class="text-secondary mb-3">
                    A Tracker can be used on multiple sites. Each Tracker has it's own plan and users.
                </div>
            @else
                <div class="row">
                    <div class="col">
                        <p>
                            Welcome!
                        </p>

                        <p>
                            If you signed-up to join an existing Tracker,
                            click on the link in the invite email you recieved.
                        </p>

                        <p>
                            Otherwise, click below to create a new Tracker
                            (free plan available) and get started!
                        </p>
                    </div>
                </div>
            @endif

            @foreach ($organizations as $Organization)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row align-items-center g-2">
                            <div class="fs-6 col-md-6">
                                {{ $Organization->name }}

                                <span class="badge bg-info text-dark ms-1">
                                    {{ $Organization->pivot->role ?? 'n/a' }}
                                </span>
                            </div>

                            <div class="text-md-center col-md-4">
                                <a href="{{ $Organization->getExploreRoute() }}"
                                class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-compass me-1"></i>
                                    Explore
                                </a>

                                <a href="{{ route('funnels.index', ['organization' => $Organization->id]) }}"
                                class="btn btn-sm btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                    Saved Funnels
                                </a>
                            </div>

                            <div class="text-md-end col-md-2">
                                <a href="{{ route('organizations.show', $Organization->id) }}"
                                class="btn btn-secondary btn-sm">
                                    <i class="fas fa-cog me-1"></i>
                                    Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('organizations.create') }}" class="btn btn-outline-dark btn-sm mt-2">
                <i class="fas fa-plus me-1"></i>
                Create New Tracker
            </a>
        </div>
    </div>
</div>
@endsection
