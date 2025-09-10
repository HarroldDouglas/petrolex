@extends('emails.layout')

@section('title', __('email.order_cancelled_subject', ['order_number' => $order->order_number]))

@section('header-title', __('email.order_cancelled_subject', ['order_number' => $order->order_number]))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.order_greeting', ['user_name' => $user->fullname ]) }}</p>

    <p>{{ __('email.order_cancelled_message', [
        'order_number' => $order->order_number
    ]) }}</p>

    <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
        <h3 style="margin-top: 0; color: #721c24;">{{ __('email.cancellation_details') }}</h3>
        <p><strong>{{ __('email.order_number') }}:</strong> {{ $order->order_number }}</p>
        <p><strong>{{ __('email.order_total') }}:</strong> {{ number_format($order->total_amount, 2) }} {{ $order->currency ?? 'FCFA' }}</p>
        <p><strong>{{ __('email.order_customer') }}:</strong> {{ $order->customer->user->name ?? __('email.unknown_customer') }}</p>
        @if($order->cancelled_at)
            <p><strong>{{ __('email.order_cancelled_at') }}:</strong> {{ $order->cancelled_at->format('d/m/Y H:i') }}</p>
        @endif
        @if($order->comments)
            <p><strong>{{ __('email.cancellation_reason') }}:</strong> {{ $order->comments }}</p>
        @endif
        <p><strong>{{ __('email.order_status') }}:</strong> <span style="color: #dc3545; font-weight: bold;">{{ $order->status->label ?? ucfirst($order->status) }}</span></p>
    </div>

    @if($order->total_amount > 0)
        <div style="background-color: #d1ecf1; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #17a2b8;">
            <p style="margin: 0; color: #0c5460;"><strong>{{ __('email.refund_info') }}:</strong> {{ __('email.order_cancelled_refund_message') }}</p>
        </div>
    @endif

    <p>{{ __('email.order_cancelled_footer_message') }}</p>

    <div style="background-color: #e2e3e5; padding: 15px; border-radius: 6px; margin: 15px 0;">
        <p style="margin: 0; text-align: center;"><strong>{{ __('email.need_help') }}</strong></p>
        <p style="margin: 5px 0 0 0; text-align: center; color: #6c757d;">{{ __('email.contact_support_message') }}</p>
    </div>
@endsection
