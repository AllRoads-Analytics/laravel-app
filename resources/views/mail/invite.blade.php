@component('mail::message')
# AllRoads Analytics Invite

You have been invited to join the _{{ $Invite->Organization->name }}_ tracker, on AllRoad Analytics.

@component('mail::button', [ 'url' => $Invite->getAcceptRoute() ])
Accept
@endcomponent

Thanks,<br>
{{ config('app.label') }}
@endcomponent
