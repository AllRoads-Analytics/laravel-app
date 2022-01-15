@php
    use Illuminate\Support\Arr;
@endphp

@extends('layouts.app')

@section('content')
<div class="container" id="vue-app">
    <div class="row">
        <div class="col">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">
                        {{ $Organization->name }}
                    </li>

                    {{-- <li class="breadcrumb-item">
                        <a href="{{ route('funnels.index', $Organization) }}">
                            Saved Funnels
                        </a>
                    </li> --}}
                </ol>
              </nav>
        </div>
    </div>

    <div class="row mt-2">
        <h1 class="m-0">
            Funnel Explorer
        </h1>
    </div>

    <div class="row mt-2">
        <div class="col">
            <pathfinder
            pixel_id="{{ $Organization->pixel_id }}"
            view_days="{{ $view_days }}"
            :limit_reached="{{ $limit_reached ? 'true' : 'false' }}"
            :can_edit="{{ $can_edit ? 'true' : 'false' }}"
            organization_id="{{ $Organization->id }}"
            ></pathfinder>
        </div>
    </div>
</div>
@endsection
