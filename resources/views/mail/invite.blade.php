@component('mail::message')
# AllRoads Analytics Invite

You have been invited to join _{{ $Invite->Organization->name }}_.

@component('mail::button', [ 'url' => $Invite->getAcceptRoute() ])
Accept
@endcomponent

Thanks,<br>
{{ config('app.label') }}
@endcomponent
