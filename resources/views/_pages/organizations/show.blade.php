@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>Manage <i>{{ $Organization->name }}</i></h1>

            <div class="d-grid gap-3">
                <div class="card">
                    <div class="card-header">
                        Tracking code
                    </div>

                    <div class="card-body">
                        <p>Add the following, before the end of the <code>{{ '<body>' }}</code> tag.</p>

                        <p>
                            <div class="form">
                                <textarea style="resize: none" readonly id="codeSnippet" class="form-control" rows="10"
                                >{{ $Organization->getTracker()->getCodeSnippet() }}</textarea>
                            </div>
                        </p>

                        <p>
                            <button class="btn btn-primary" onclick="copyCode()">Copy to Clipboard</button>
                        </p>
                    </div>
                </div>

                @if (auth()->user()->can('manage', $Organization))
                    <div class="card">
                        <div class="card-header">
                            Users
                        </div>

                        <div class="card-body">
                            <div>
                                <ul>
                                    @foreach ($Organization->Users()->withPivot('role')->get()->sortBy('name') as $User)
                                        <form action="{{ route('organizations.users.remove', [
                                            'organization' => $Organization->id,
                                            'user' => $User->id,
                                        ]) }}"
                                        method="post"
                                        onsubmit="confirm('Are you sure you want to remove this user?')">
                                            @csrf

                                            <li>
                                                {{ $User->name }} | {{ $User->email }}

                                                <span class="badge rounded-pill bg-dark">
                                                    {{ $User->pivot->role }}
                                                </span>

                                                @if ($User->id === auth()->user()->id)

                                                @else
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        X
                                                    </button>
                                                @endif
                                            </li>
                                        </form>
                                    @endforeach
                                </ul>
                            </div>

                            @if ($Organization->Invites->count())
                                <p class="mt-3 mb-1"><i>Invites:</i></p>
                            @endif

                            <div>
                                <ul>
                                    @foreach ($Organization->Invites->sortBy('email') as $Invite)
                                        <li class="fst-italic">{{ $Invite->email }}</li>
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
                                class="col-md-6">
                                    <form action="{{ route('organizations.invites.create', ['organization' => $Organization->id]) }}"
                                    method="post"
                                    class="d-grid gap-2">
                                        @csrf

                                        <input type="email" name="email" id="email" class="form-control"
                                        placeholder="Email">

                                        <div>
                                            <button type="submit" class="btn btn-success">
                                                Invite
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header">
                            Plan/Billing
                        </div>

                        <div class="card-body">
                            <p>// todo</p>

                            <p>
                                <form action="{{ route('organizations.destroy', $Organization->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </p>
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
