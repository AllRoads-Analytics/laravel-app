@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Path</th>
                        <th scope="col">Views</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td>
                                {{ $page['path'] }}
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
