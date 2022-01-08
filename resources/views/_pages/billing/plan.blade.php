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
                @php
                    $allowed = in_array($id, $allowed_plan_ids);
                @endphp

                <div class="col-md">
                    <div class="card">
                        <label for="{{ $id }}" style="cursor: pointer;">
                            <div class="card-header bg-light text-dark fw-bold fs-5">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        {{ $plan['label'] }}
                                    </div>



                                    <div>
                                        <input type="radio" name="plan" class="form-check-input" id="{{ $id }}"
                                        value="{{ $id }}"
                                        {{ $Plan->id === $id ? 'checked' : '' }}
                                        {{ $allowed ? '' : 'disabled'}}>
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

                            @if ( ! $allowed)
                                <div class="alert alert-danger small px-2 py-1 m-0">
                                    Current usage is too high for this plan.
                                </div>
                            @elseif ('free' === $id && $Plan && $Plan->id !== 'free')
                                <div class="alert alert-secondary small px-2 py-1 m-0">
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

        <div class="row mt-4 text-muted text-center">
            <div class="col">
                To cancel subscription and delete account, please email
                <a href="mailto:{{ config('allroads.contact_email') }}"
                class="text-reset">
                    {{ config('allroads.contact_email') }}
                </a>
            </div>
        </div>
    </div>
</form>
@endsection
