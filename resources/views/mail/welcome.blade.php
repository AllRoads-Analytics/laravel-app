@component('mail::message')
# Welcome to AllRoads Analytics

Create a new organization to get your tracking code, or view the organization you've joined's data!

@component('mail::button', [ 'url' => route('home') ])
Get Started
@endcomponent

Thanks,<br>
{{ config('app.label') }}
@endcomponent
