@extends('emails.layout')

@section('title', __('email.order_cancelled_refund_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_cancelled_refund_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_cancelled_refund_intro', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
        <h3 style="margin-top: 0; color: #155724;">{{ __('email.refund_confirmed') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.refund_amount') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($refundAmount) }}</p>
        <p><strong>{{ __('email.new_wallet_balance') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($newBalance) }}</p>
        @if($order->cancelled_at)
            <p><strong>{{ __('email.order_cancelled_at') }}:</strong> {{ $order->cancelled_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <div style="background-color: #d1ecf1; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #17a2b8;">
        <p style="margin: 0; color: #0c5460;">
            <strong>{{ __('email.refund_wallet_info') }}:</strong> 
            {{ __('email.refund_wallet_message', ['amount' => \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($refundAmount)]) }}
        </p>
    </div>

    <p>{{ __('email.refund_reuse_message') }}</p>

    <div style="background-color: #e2e3e5; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; text-align: center;"><strong>{{ __('email.need_help') }}</strong></p>
        <p style="margin: 5px 0 0 0; text-align: center; color: #6c757d;">{{ __('email.contact_support_message') }}</p>
    </div>
@endsection
