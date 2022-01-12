@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <p class="m-0 text-secondary">
                {{ $Tracker->Organization->name }}
            </p>

            <h2>Saved Funnels</h2>

            <div>
                @livewire('funnels', ['Organization' => $Organization])
            </div>

            @if ($Organization->getPlanUsage()->limitReached('limit_funnels'))
                <div class="mt-3 row">
                    <div class="col-md-5">
                        <x-plan-limit-reached :organization="$Organization">
                            Funnel limit reached.
                        </x-plan-limit-reached>
                    </div>
                </div>
            @else
                <a href="{{ route('pathfinder.tracker', $Tracker->pixel_id) }}" class="btn btn-success mt-2">
                    <i class="fas fa-compass me-1"></i>
                    New Funnel Explorer
                </a>
            @endif
        </div>
    </div>
</div>

@livewireScripts
@endsection
