@extends('emails.layout')

@section('title', __('email.order_processing_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_processing_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_processing_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #cce5ff; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff;">
        <h3 style="margin-top: 0; color: #004085;">{{ __('email.order_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->format($order->total_amount) }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->fullname ?? __('email.unknown_customer') }}</p>
        @if($order->processing_at)
            <p><strong>{{ __('email.order_processing_at') }}:</strong> {{ $order->processing_at->format('d/m/Y H:i') }}</p>
        @endif
        @if($order->delivery_address)
            <p><strong>{{ __('email.order_delivery_address') }}:</strong> {{ $order->delivery_address }}</p>
        @endif
        @if($order->delivery_person)
            <p><strong>{{ __('email.delivery_person') }}:</strong> {{ $order->delivery_person->user->name }}</p>
            @if($order->delivery_person->user->phone)
                <p><strong>{{ __('email.delivery_person_phone') }}:</strong> {{ $order->delivery_person->user->phone }}</p>
            @endif
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #007bff; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    <div style="background-color: #fff3cd; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #ffc107;">
        <p style="margin: 0; color: #856404;"><strong>{{ __('email.estimated_delivery') }}:</strong> {{ __('email.order_processing_delivery_info') }}</p>
    </div>

    <p>{{ __('email.order_processing_footer_message') }}</p>
@endsection
