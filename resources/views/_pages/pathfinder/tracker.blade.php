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
                    <li class="breadcrumb-item active">
                        {{ $Tracker->Organization->name }}
                    </li>
                </ol>
              </nav>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <pathfinder
            pixel_id="{{ $Tracker->pixel_id }}"
            ></pathfinder>
        </div>
    </div>
</div>
@endsection
