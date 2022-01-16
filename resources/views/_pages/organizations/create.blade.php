@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="m-0">Create new tracker</h1>

            <div class="text-muted mb-3">
                A Tracker can be used on multiple sites. Each Tracker has it's own plan and users.
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('organizations.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Tracker Name
                            </label>

                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                            aria-describedby="trackerHelp"
                            id="name" name="name">

                            <div id="trackerHelp" class="form-text mt-1">
                                Company or owner of site(s) where tracker will be used.
                            </div>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
