@component('mail::message')
# Statut de la commande mis à jour

Bonjour {{ $order->distributionCenter->manager->user->name ?? 'Responsable' }},

Le statut de la commande #{{ $order->order_number }} dans votre centre de distribution a été mis à jour à **{{ $order->status->label() }}**.

Vous pouvez consulter les détails de la commande en cliquant sur le bouton ci-dessous :

@component('mail::button', ['url' => url('/orders/' . $order->id . '/details')])
Voir la commande
@endcomponent

Cordialement,
{{ config('app.name') }}
@endcomponent
