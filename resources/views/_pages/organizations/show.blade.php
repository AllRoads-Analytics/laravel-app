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

                <div class="card">
                    <div class="card-header">
                        Users
                    </div>

                    <div class="card-body">
                        <ul>
                            @foreach ($Organization->Users as $User)
                                <li>{{ $User->name }}</li>
                            @endforeach
                        </ul>
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
