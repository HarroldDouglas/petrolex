@component('mail::message')
# Statut de votre commande mis à jour

Bonjour {{ $order->customer->user->name ?? 'Client' }},

Le statut de votre commande #{{ $order->order_number }} a été mis à jour à **{{ $order->status->label() }}**.

Vous pouvez consulter les détails de votre commande en cliquant sur le bouton ci-dessous :

@component('mail::button', ['url' => url('/orders/' . $order->id . '/details')])
Voir la commande
@endcomponent

Merci d'avoir choisi nos services.

Cordialement,
{{ config('app.name') }}
@endcomponent
