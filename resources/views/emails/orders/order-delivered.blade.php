@extends('emails.layout')

@section('title', __('email.order_delivered_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_delivered_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <div style="text-align: center; margin: 20px 0;">
        <h2 style="color: #28a745; margin: 0;">🎉 {{ __('email.order_delivered_celebration') }}</h2>
    </div>

    <p>{{ __('email.order_delivered_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
        <h3 style="margin-top: 0; color: #155724;">{{ __('email.delivery_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->fullname ?? __('email.unknown_customer') }}</p>
        @if($order->delivered_at)
            <p><strong>{{ __('email.order_delivered_at') }}:</strong> {{ $order->delivered_at->format('d/m/Y H:i') }}</p>
        @endif
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        @if($order->delivery_person)
            <p><strong>{{ __('email.delivered_by') }}:</strong> {{ $order->delivery_person->user->name }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #28a745; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; border: 2px dashed #6c757d;">
        <p style="margin: 0; text-align: center;"><strong>{{ __('email.feedback_request') }}</strong></p>
        <p style="margin: 5px 0 0 0; text-align: center; color: #6c757d;">{{ __('email.order_delivered_feedback_message') }}</p>
    </div>

    <p>{{ __('email.order_delivered_footer_message') }}</p>

    <p style="color: #28a745; font-weight: bold;">{{ __('email.thank_you_for_business') }}</p>
@endsection
