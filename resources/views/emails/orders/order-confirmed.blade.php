@extends('emails.layout')

@section('title', __('email.order_confirmed_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_confirmed_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_confirmed_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
        <h3 style="margin-top: 0; color: #155724;">{{ __('email.order_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ number_format($order->total_amount, 2) }} {{ $order->currency ?? 'FCFA' }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->name ?? __('email.unknown_customer') }}</p>
        @if($order->confirmed_at)
            <p><strong>{{ __('email.order_confirmed_at') }}:</strong> {{ $order->confirmed_at->format('d/m/Y H:i') }}</p>
        @endif
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #28a745; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #d1ecf1; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #17a2b8;">
        <p style="margin: 0; color: #0c5460;"><strong>{{ __('email.next_steps') }}:</strong> {{ __('email.order_confirmed_next_steps') }}</p>
    </div>

    <p>{{ __('email.order_confirmed_footer_message') }}</p>
@endsection
