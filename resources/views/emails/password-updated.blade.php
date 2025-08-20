@extends('emails.layout')

@section('title', 'Mise à jour de votre mot de passe')

@section('header-title', 'Mise à jour de votre mot de passe')

@section('content')
    <p>Bonjour {{ $user->first_name }},</p>

    <p>Votre mot de passe a été mis à jour avec succès.</p>

    <p>Si vous n'êtes pas à l'origine de ce changement, veuillez nous contacter immédiatement.</p>
@endsection

