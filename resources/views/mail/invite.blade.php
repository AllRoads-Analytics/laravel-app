@component('mail::message')
# Pathfinder Invite

You have been invited to join _{{ $Invite->Organization->name }}_.

@component('mail::button', [ 'url' => $Invite->getAcceptRoute() ])
Accept
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
