@php
    use Illuminate\Support\Arr;
@endphp

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('pathfinder.tracker', $Tracker->pixel_id) }}">
                            {{ $Tracker->pixel_id }}
                        </a>
                    </li>

                    @if ( ! count($previous_pages))
                        <li class="breadcrumb-item active">
                            {{ $host }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ route('pathfinder.tracker.host', [$Tracker->pixel_id, $host]) }}">
                                {{ $host }}
                            </a>
                        </li>
                    @endif
                </ol>
              </nav>
        </div>
    </div>

    @if (count($previous_pages))
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Funnel</h2>
                    </div>

                    <div class="card-body">
                        @if ( ! $funnel_pages)
                            <span class="fst-italic">Begin new funnel by selecting starting page, below.</span>
                        @else
                            <div class="d-flex flex-wrap p-1">
                                @foreach ($funnel_pages as $idx => $page_views)
                                    <div class="p-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <p class="fw-bold">{{ $idx + 1 }}</p>
                                                <p>Page: {{ $page_views['page'] }}</p>
                                                <p>Views: {{ $page_views['views'] }}</p>

                                                <a href="{{ route('pathfinder.tracker.host', [
                                                    'tracker_pixel_id' => $Tracker->pixel_id,
                                                    'host' => $host,
                                                    'previous_pages' => array_values(
                                                            Arr::where( $previous_pages, fn($_page) => ( $_page !== $page_views['page'] ) )
                                                        ),
                                                ]) }}">
                                                    Remove
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>
                {{ $previous_pages ? 'Select next page in funnel' : 'Select page to begin funnel'}}:
            </h2>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Path</th>
                        <th scope="col">Views</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pageviews as $page)
                        <tr>
                            <td>
                                <a href="{{ route('pathfinder.tracker.host', [
                                    'tracker_pixel_id' => $Tracker->pixel_id,
                                    'host' => $host,
                                    'previous_pages' => array_merge( $previous_pages, [ $page['path'] ] )
                                ]) }}">
                                    {{ $page['path'] }}
                                </a>
                            </td>

                            <td>
                                {{ $page['views'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
