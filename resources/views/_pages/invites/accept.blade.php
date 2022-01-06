@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center text-center">
        @if ( ! $already_belongs)
            <div class="col-md-8">
                <h2 class="mb-5">
                    You have been invited to join
                    <br>
                    <i>{{ $Organization->name }}</i>.
                </h2>

                @if (auth()->user())
                    <form method="POST">
                        @csrf

                        <button type="submit" class="btn btn-primary">Accept</button>
                    </form>
                @else
                    <h5>
                        Please
                        <a href="{{ route('login') }}">{{ __('login') }}</a>
                        or
                        <a href="{{ route('register') }}">{{ __('sign up') }}</a>
                        to accept.
                    </h5>
                @endif

            </div>
        @else
            <div class="col-md-8">
                <h1>Invitation</h1>

                You already belong to
                <br>
                {{ $Organization->name }}
            </div>
        @endif

    </div>
@endsection
