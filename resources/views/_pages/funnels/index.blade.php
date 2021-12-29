@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <p class="m-0 text-secondary">
                {{ $Tracker->Organization->name }}
            </p>

            <h2>Funnels</h2>

            <div>
                @livewire('funnels', ['Organization' => $Organization])
            </div>

            <a href="{{ route('pathfinder.tracker', $Tracker->pixel_id) }}" class="btn btn-success mt-2">
                <i class="fas fa-plus"></i>
                New Funnel
            </a>
        </div>
    </div>
</div>

@livewireScripts
@endsection
