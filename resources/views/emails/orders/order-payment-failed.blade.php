@extends('emails.layout')

@section('title', __('email.order_payment_failed_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_payment_failed_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_payment_failed_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
        <h3 style="margin-top: 0; color: #721c24;">{{ __('email.payment_failure_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ number_format($order->total_amount, 2) }} {{ $order->currency ?? 'FCFA' }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->name ?? __('email.unknown_customer') }}</p>
        <p><strong>{{ __('email.failure_date') }}:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #dc3545; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #ffc107;">
        <p style="margin: 0; color: #856404;"><strong>{{ __('email.possible_reasons') }}:</strong></p>
        <ul style="margin: 10px 0 0 20px; color: #856404;">
            <li>{{ __('email.insufficient_funds') }}</li>
            <li>{{ __('email.expired_card') }}</li>
            <li>{{ __('email.network_issue') }}</li>
            <li>{{ __('email.bank_decline') }}</li>
        </ul>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <div style="background-color: #007bff; color: white; padding: 12px 24px; border-radius: 6px; display: inline-block;">
            <strong>{{ __('email.retry_payment_now') }}</strong>
        </div>
    </div>

    <p>{{ __('email.order_payment_failed_footer_message') }}</p>

    <div style="background-color: #e2e3e5; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; text-align: center;"><strong>{{ __('email.need_help') }}</strong></p>
        <p style="margin: 5px 0 0 0; text-align: center; color: #6c757d;">{{ __('email.payment_support_message') }}</p>
    </div>
@endsection
