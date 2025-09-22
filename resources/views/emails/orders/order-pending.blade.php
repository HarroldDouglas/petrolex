@extends('emails.layout')

@section('title', __('email.order_pending_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_pending_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_pending_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
        <h3 style="margin-top: 0; color: #856404;">{{ __('email.payment_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->fullname ?? __('email.unknown_customer') }}</p>
        <p><strong>{{ __('email.order_date') }}:</strong> {{ $order->order_date->format('d/m/Y H:i') }}</p>
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #856404; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #f8d7da; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #dc3545;">
        <p style="margin: 0; color: #721c24;"><strong>{{ __('email.urgent') }}:</strong> {{ __('email.order_pending_payment_warning') }}</p>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <div style="background-color: #007bff; color: white; padding: 12px 24px; border-radius: 6px; display: inline-block;">
            <strong>{{ __('email.complete_payment_now') }}</strong>
        </div>
    </div>

    <p>{{ __('email.order_pending_footer_message') }}</p>

    <div style="background-color: #e2e3e5; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; text-align: center; color: #6c757d;">{{ __('email.order_pending_timeout_warning') }}</p>
    </div>
@endsection
