@php
    use Illuminate\Support\Arr;
@endphp

@extends('layouts.app')

@section('content')
<div class="container" id="vue-app">
    <div class="row">
        <div class="col">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('pathfinder.tracker', $Tracker) }}">
                            {{ $Tracker->Organization->name }}
                        </a>
                    </li>

                    {{-- @if ( ! count($previous_pages))
                        <li class="breadcrumb-item active">
                            {{ $host }}
                        </li>
                    @else --}}
                        <li class="breadcrumb-item">
                            <a href="{{ route('pathfinder.tracker.host', [$Tracker, $host]) }}">
                                {{ $host }}
                            </a>
                        </li>
                    {{-- @endif --}}
                </ol>
              </nav>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <pathfinder
            pixel_id="{{ $Tracker->pixel_id }}"
            host="{{ $host }}"
            ></pathfinder>
        </div>
    </div>
</div>
@endsection
