@extends('emails.layout')

@section('title', __('email.order_notification_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_notification_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_status_message', [
        'order_number' => $order->order_number,
        'status' => \App\Enums\OrderStatus::labels()[strtoupper($order->status)] ?? ucfirst($order->status)
    ]) }}</p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0;">{{ __('email.order_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ number_format($order->total_amount, 2) }} {{ $order->currency ?? 'FCFA' }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->name ?? __('email.unknown_customer') }}</p>
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        @if($order->delivered_at)
            <p><strong>{{ __('email.order_delivered_at') }}:</strong> {{ $order->delivered_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <p>{{ __('email.order_footer_message') }}</p>
@endsection