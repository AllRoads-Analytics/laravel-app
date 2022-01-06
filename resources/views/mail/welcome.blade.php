@component('mail::message')
# Welcome to AllRoads Analytics

Create or join an organization, install your tracking code, and start measuring all of the paths users take on your site(s)!

@component('mail::button', [ 'url' => route('home') ])
Get Started
@endcomponent

Thanks,<br>
{{ config('app.label') }}
@endcomponent
