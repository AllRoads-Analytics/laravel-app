@php
    use \App\Models\User;
@endphp

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @error('name')
                <div class="">
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                </div>
            @enderror

            <div class="mb-3" x-data="{ show: false}">
                <h1 class="fs-3 m-0"
                x-show=" ! show">
                    Manage tracker:
                    <i>{{ $Organization->name }}</i>

                    <button class="btn btn-sm btn-link"
                    x-on:click="show = true">
                        <i class="fas fa-edit"></i>
                    </button>
                </h1>

                <form action="{{ route('organizations.update', $Organization->id) }}"
                method="POST"
                x-show="show" x-cloak>
                    @csrf
                    @method('PUT')

                    <div class="input-group mb-1">
                        <input type="text" class="form-control"
                        name="name"
                        placeholder="Tracker name"
                        aria-label="Tracker name"
                        aria-describedby="button-name"
                        value="{{ $Organization->name }}">

                        <button class="btn btn-success" type="submit" id="button-name">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>

                    <div>
                        <button class="btn btn-link text-secondary" type="button"
                        x-on:click="show = false">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <div class="d-grid gap-3">
                <div class="card">
                    <div class="card-header fs-5 fw-bold">
                        Tracking code
                    </div>

                    <div class="card-body">
                        <p class="mb-2">
                            Add the following, before the end of the <code>{{ '<body>' }}</code> tag,
                            <b>on all sites</b> you wish to track.
                        </p>

                        <div class="form">
                            <textarea style="resize: none" readonly id="codeSnippet" class="form-control bg-light" rows="10"
                            >{{ $Organization->getCodeSnippet() }}</textarea>
                        </div>

                        <div class="mt-2">
                            <button class="btn btn-primary" onclick="copyCode()">
                                <i class="fas fa-copy"></i>
                                Copy to Clipboard
                            </button>
                        </div>

                        <p class="mt-4">
                            By default cross-site-tracking is turned on. Cross-site-tracking is accomplished by adding a parameter
                            to links to pages outside of the site of the current page.
                            To disable this functionality, change
                            <code>{follow:true}</code>
                            to
                            <code>{follow:false}</code>.
                        </p>

                        <p class="mt-4 mb-2">
                            For Single Page Applications (SPAs), call the following JavaScript code each time the URL path changes.
                        </p>

                        <p class="m-0">
                            <div class="form col-md-6">
                                <input style="resize: none" disabled class="form-control bg-light"
                                value="pathfinder(&quot;event&quot;, &quot;pageview&quot;);" />
                            </div>
                        </p>
                    </div>
                </div>

                @if (auth()->user()->can('manage', $Organization))
                    <div class="card">
                        <div class="card-header fs-5 fw-bold">
                            Users
                        </div>

                        <div class="card-body">
                            <div>
                                <ul class="list-unstyled">
                                    @foreach ($Organization->Users()->withPivot('role')->get()->sortBy('name') as $User)
                                        <li class="mb-2 pb-1 border-bottom" x-data="{ open: false }">
                                            {{ $User->name }} &ndash; {{ $User->email }}

                                            <span class="badge rounded-pill bg-dark me-2"
                                            x-show=" ! open">
                                                {{ $User->pivot->role }}
                                            </span>

                                            <div class="col-lg-6">
                                                <form action="{{ route('organizations.users.edit', [
                                                    'organization' => $Organization->id,
                                                    'user' => $User->id,
                                                ]) }}"
                                                class="d-inline"
                                                method="post">
                                                    @csrf

                                                    <span x-cloak x-show="open">
                                                        <select name="role" id="role" class="form-select" required>
                                                            @foreach (User::ROLES as $role)
                                                                <option {{ $role === $User->pivot->role ? 'selected' : '' }}
                                                                value="{{ $role }}">
                                                                    {{ $role }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <div class="mt-1">
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                Update
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-link"
                                                            x-on:click="open = false">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </span>
                                                </form>

                                                @if ($User->id === auth()->user()->id)
                                                    <span class="ms-2">(you)</span>
                                                @else
                                                    <button class="btn btn-link text-primary p-1"
                                                    type="button"
                                                    x-on:click="open = true"
                                                    x-show=" ! open">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <form action="{{ route('organizations.users.remove', [
                                                        'organization' => $Organization->id,
                                                        'user' => $User->id,
                                                    ]) }}"
                                                    class="d-inline"
                                                    method="post"
                                                    onsubmit="return confirm('Are you sure you want to remove this user?')">
                                                        @csrf
                                                        <button class="btn btn-link text-danger p-1" type="submit"
                                                        x-show=" ! open">
                                                            <i class="fas fa-minus-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            @if ($Organization->Invites->count())
                                <p class="mt-3 mb-1"><i>Invites:</i></p>
                            @endif

                            <div>
                                <ul>
                                    @foreach ($Organization->Invites->sortBy('email') as $Invite)
                                        <form action="{{ route('organizations.invites.remove', [
                                            'organization' => $Organization->id,
                                            'invite' => $Invite->id,
                                        ]) }}"
                                        method="post"
                                        onsubmit="return confirm('Are you sure you want to remove this invite?')">
                                            @csrf

                                            <li class="fst-italic">
                                                {{ $Invite->email }}

                                                (<a href="{{ $Invite->getAcceptRoute() }}">Invite Link</a>)

                                                <button class="btn btn-link text-danger p-1" type="submit">
                                                    <i class="fas fa-minus-circle"></i>
                                                </button>
                                            </li>
                                        </form>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mt-3"
                            x-data="{ open: false }">
                                @if ($Organization->getPlanUsage()->limitReached('limit_users'))
                                    <div class="mt-3 row">
                                        <div class="col-md-6">
                                            <x-plan-limit-reached :organization="$Organization">
                                                User limit reached.
                                            </x-plan-limit-reached>
                                        </div>
                                    </div>
                                @else
                                    <button class="btn btn-success"
                                    x-on:click="open = true" x-show=" ! open">
                                        <i class="fas fa-plus"></i>
                                        Add
                                    </button>
                                @endif

                                <div x-show="open" x-cloak
                                class="col-lg-6">
                                    <form action="{{ route('organizations.invites.create', ['organization' => $Organization->id]) }}"
                                    method="post"
                                    class="d-grid gap-2">
                                        @csrf

                                        <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Email" required>

                                        <select name="role" id="role" class="form-select" required>
                                            <option value="">-- Role --</option>

                                            @foreach (User::ROLES as $role)
                                                <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>

                                        <div class="gx-3">
                                            <button type="submit" class="btn btn-success">
                                                Invite
                                            </button>

                                            <button type="button" class="btn btn-link"
                                            x-on:click="open = false">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header fs-5 fw-bold">
                            Plan / Billing / Usage
                        </div>

                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-5">
                                    <h5 class="mb-2">Current Plan</h5>

                                    <div class="d-flex gap-2 align-items-center">
                                        <div>
                                            <span class="badge bg-info text-black fs-6">
                                                {{ $Plan->label }}
                                            </span>
                                        </div>

                                        <div class="">
                                            ${{ number_format($Plan->monthly_price, 2) }}
                                            per month
                                        </div>
                                    </div>

                                    @if ($endsAt = $Organization->subscription('default')->ends_at ?? false)
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-black fs-6">
                                                <i class="fas fa-stopwatch"></i>
                                                Switches to "Free" on
                                                {{ $endsAt->format('M jS') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($PaymentCard)
                                        <h5 class="mt-4 mb-2">Payment Method</h5>

                                        <div class="d-flex flex-wrap gap-3 align-items-center">
                                            <div class="fs-3">
                                                {!! $PaymentCard->getBrandIcon() !!}
                                            </div>

                                            <div>
                                                {!! $PaymentCard->getObscuredNumber() !!}
                                            </div>

                                            <div>
                                                Exp: {!! $PaymentCard->getExpirationDate() !!}
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <a href="{{ route('organizations.billing.get_update_payment_method', $Organization) }}"
                                            class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-wrench"></i>
                                                Replace Payment Method
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-7">
                                    <h5 class="mb-2">Usage</h5>

                                    <div class="d-grid gap-2">
                                        @foreach ($usages as $usage)
                                            <div>
                                                <div class="">
                                                    {{ $usage['label'] }}:
                                                    {{ number_format($usage['usage']) }} / {{ number_format($usage['limit']) }}
                                                </div>

                                                <div class="mt-1">
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info text-black"
                                                        aria-valuenow="{{ $usage['usage'] }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="{{ $usage['limit'] }}"
                                                        style="width: {{ (string) $usage['percentage'] }}%;"
                                                        >{{ (string) $usage['percentage'] }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="">
                                            Data retention:
                                            {{ $Plan->limit_data_view_days }}
                                            days
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('organizations.billing.get_select_plan', $Organization) }}"
                                        class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-wrench"></i>
                                            Update Plan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="card card-danger text-center">
                        <div class="card-header fs-5 fw-bold bg-danger text-white text-start">
                            Danger Zone
                        </div>

                        <div class="card-body">
                            <div>
                                <form action="{{ route('organizations.destroy', $Organization->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this Organization?')">
                                    @csrf
                                    @method('DELETE')

                                    <div>
                                        PERMANENTLY DELETE this Organization:
                                    </div>

                                    <button class="btn btn-danger mt-1">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div> --}}
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function copyCode() {
        /* Get the text field */
        var copyText = document.getElementById("codeSnippet");

        /* Select the text field */
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* For mobile devices */

        /* Copy the text inside the text field */
        navigator.clipboard.writeText(copyText.value);

        copyText.setSelectionRange(0,0);
    }
</script>
@endsection
