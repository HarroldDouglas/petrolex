@extends('emails.layout')

@section('title', 'Votre code d\'authentification')

@section('header-title', 'Code d\'authentification')

@section('content')
    <p>Bonjour,</p>
    <p>Nous avons reçu une demande de vérification de votre identité pour {{ $maskedIdentifier }}. Veuillez utiliser le code suivant pour
        compléter le processus :</p>

    <div class="otp-container">
        <div class="otp-code">{{ $otp }}</div>
    </div>

    <p>Ce code expirera dans <strong>10 minutes</strong> pour des raisons de sécurité.</p>
    <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet e-mail ou contacter notre équipe de support si vous avez des préoccupations.</p>
@endsection

