<?php

namespace App\Exports;

use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\Exports\BaseExport;

class UsersExport extends BaseExport
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getHeadings(): array
    {
        return [
            'ID',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Actif',
            'Dernière connexion',
            'Créé le',
        ];
    }

    protected function mapRow($user): array
    {
        return [
            $user->id,
            $user->last_name,
            $user->first_name,
            $user->email,
            $user->phone_number,
            $this->formatBoolean($user->is_active),
            $this->formatDateTime($user->last_login_at),
            $this->formatDate($user->created_at),
        ];
    }
}
