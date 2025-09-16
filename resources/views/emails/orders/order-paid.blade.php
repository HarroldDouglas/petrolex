@extends('emails.layout')

@section('title', __('email.order_paid_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_paid_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <div style="text-align: center; margin: 20px 0;">
        <h2 style="color: #28a745; margin: 0;">✅ {{ __('email.payment_confirmed') }}</h2>
    </div>

    <p>{{ __('email.order_paid_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
        <h3 style="margin-top: 0; color: #155724;">{{ __('email.payment_confirmation_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.amount_paid') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->name ?? __('email.unknown_customer') }}</p>
        <p><strong>{{ __('email.payment_date') }}:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #28a745; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #d1ecf1; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #17a2b8;">
        <p style="margin: 0; color: #0c5460;"><strong>{{ __('email.next_steps') }}:</strong> {{ __('email.order_paid_next_steps') }}</p>
    </div>

    <p>{{ __('email.order_paid_footer_message') }}</p>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; border: 1px solid #dee2e6;">
        <p style="margin: 0; text-align: center;"><strong>{{ __('email.payment_receipt') }}</strong></p>
        <p style="margin: 5px 0 0 0; text-align: center; color: #6c757d;">{{ __('email.keep_receipt_message') }}</p>
    </div>
@endsection
