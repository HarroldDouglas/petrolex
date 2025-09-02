@php
    // Set locale based on user language
    $currentLocale = app()->getLocale();
    if ($userLanguage) {
        app()->setLocale($userLanguage);
    }
@endphp

@extends('emails.layout')

@section('title', __('email.otp_subject'))

@section('header-title', __('email.otp_subject'))

@section('footer')
    <p>{{ __('email.regards') }}</p>
    <p><strong>{{ __('email.team_signature', ['app' => config('app.name')]) }}</strong></p>
@endsection

@section('content')
    <p>{{ __('email.otp_greeting') }}</p>
    <p>{{ __('email.otp_message', ['app' => config('app.name')]) }} {{ $maskedIdentifier }}. {{ __('email.use_code_message') }}:</p>

    <div class="otp-container">
        <strong class="otp-code">{{ $otp }}</strong>
    </div>

    <p>{{ __('email.otp_expire') }}</p>
    <p>{{ __('email.otp_security') }}</p>
    
    <div>{!! __('email.otp_footer', ['app' => config('app.name')]) !!}</div>
@endsection

@php
    // Restore original locale
    app()->setLocale($currentLocale);
@endphp