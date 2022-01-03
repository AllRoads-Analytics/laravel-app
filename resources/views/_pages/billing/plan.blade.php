@extends('layouts.app')

@section('content')
<form action="" method="POST">
    @csrf

    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="m-0">Select Plan</h1>
                    </div>

                    <div>
                        <button class="btn btn-primary btn px-4" type="submit">
                            Select
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            @foreach ($plans as $id => $plan)
                <div class="col-md">
                    <div class="card">
                        <label for="{{ $id }}" style="cursor: pointer;">
                            <div class="card-header bg-primary-z text-white-z fw-bold fs-5">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        {{ $plan['label'] }}
                                    </div>

                                    <div>
                                        <input type="radio" name="plan" class="form-check-input" id="{{ $id }}"
                                        value="{{ $id }}"
                                        {{ $Plan->id === $id ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <div class="card-body d-grid gap-3">
                            <div class="text-center mb-2">
                                <span class="fs-1">${{ $plan['monthly_price'] }}</span>
                                per month
                            </div>

                            <div>
                                <i class="fas fa-filter me-1"></i>
                                {{ $plan['limit_funnels'] ?: 'Unlimited' }} saved funnels
                            </div>

                            <div>
                                <i class="fas fa-calendar me-1"></i>
                                {{ $plan['limit_data_view_days'] ?: 'Unlimited' }} days data retention
                            </div>

                            <div>
                                <i class="fas fa-users me-1"></i>
                                {{ $plan['limit_users'] ?: 'Unlimited' }} users
                            </div>

                            <div>
                                <i class="fas fa-eye me-1"></i>
                                {{ number_format($plan['limit_pageviews_per_month']) }} pageviews per month
                            </div>

                            @if ($Plan->id === $id)
                                <div class="text-center">
                                    <span class="badge bg-info text-black fs-6">
                                        Current Plan
                                    </span>
                                </div>
                            @endif

                            @if ('free' === $id && $Plan && $Plan->id !== 'free')
                                <div class="alert alert-secondary p-2 m-0" role="alert">
                                    Will stay on current plan until end of billing cycle.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <button class="btn btn-primary btn-lg" type="submit" style="width: 100%; max-width: 300px;">
                Select
            </button>
        </div>
    </div>
</form>
@endsection
