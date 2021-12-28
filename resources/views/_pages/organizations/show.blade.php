@php
    use \App\Models\User;
@endphp

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1>Manage <i>{{ $Organization->name }}</i></h1>

            <div class="d-grid gap-3">
                <div class="card">
                    <div class="card-header fs-5 fw-bold">
                        Tracking code
                    </div>

                    <div class="card-body">
                        <p>Add the following, before the end of the <code>{{ '<body>' }}</code> tag.</p>

                        <p>
                            <div class="form">
                                <textarea style="resize: none" readonly id="codeSnippet" class="form-control bg-light" rows="10"
                                >{{ $Organization->getTracker()->getCodeSnippet() }}</textarea>
                            </div>
                        </p>

                        <div>
                            <button class="btn btn-primary" onclick="copyCode()">Copy to Clipboard</button>
                        </div>
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
                                <button class="btn btn-success"
                                x-on:click="open = true" x-show=" ! open">
                                    Add
                                </button>

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
                            Plan/Billing
                        </div>

                        <div class="card-body">
                            <p>// todo</p>

                            <div>
                                <form action="{{ route('organizations.destroy', $Organization->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
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
