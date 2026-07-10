@extends('emails.layout')

@section('title', __('email.order_assigned_delivery_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_assigned_delivery_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname]) }}</p>

    <p>{{ __('email.order_assigned_delivery_message', ['order_number' => $order->order_number]) }}</p>

    <div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
        <h3 style="margin-top: 0; color: #155724;">{{ __('email.order_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->fullname ?? __('email.unknown_customer') }}</p>
        @if($order->customer?->user?->phone_number)
            <p><strong>{{ __('email.order_customer_phone') }}:</strong> {{ $order->customer->user->phone_number }}</p>
        @endif
        <p><strong>{{ __('email.order_total') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</p>
        <p><strong>{{ __('email.order_date') }}:</strong> {{ $order->order_date?->format('d/m/Y H:i') }}</p>
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong>
                {{ $order->delivery_address->address }},
                {{ $order->delivery_address->neighborhood?->name }},
                {{ $order->delivery_address->city?->name }}
            </p>
        @endif
        <p><strong>{{ __('email.order_delivery_type') }}:</strong> {{ $order->delivery_type->label }}</p>
    </div>

    <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #ffc107;">
        <p style="margin: 0; color: #856404;"><strong>{{ __('email.note') }}:</strong> {{ __('email.order_assigned_delivery_note') }}</p>
    </div>
@endsection
