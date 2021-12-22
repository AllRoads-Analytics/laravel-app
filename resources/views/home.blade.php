@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if ($organizations->count())
                <h3>Your Organizations</h3>
            @endif

            @foreach ($organizations as $Organization)
                <a href="{{ $Organization->getTracker()->getRoute() }}">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $Organization->name }}
                                </div>

                                <div>
                                    <a href="{{ route('organizations.show', $Organization->id) }}"
                                    class="btn btn-primary btn-sm">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach

            <a href="{{ route('organizations.create') }}" class="btn btn-primary">
                Create New Organization
            </a>
        </div>
    </div>
</div>
@endsection
