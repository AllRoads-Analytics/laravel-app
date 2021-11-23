@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>Create new organization</h1>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('organizations.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="input_company" class="form-label">
                                Organization Name
                            </label>

                            <input type="text" class="form-control @error('company') is-invalid @enderror" id="input_company" name="company">

                            @error('company')
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
