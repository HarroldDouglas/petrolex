<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return User::whereIn('id', $this->users)->get();
    }

    public function headings(): array
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

    public function map($user): array
    {
        return [
            $user->id,
            $user->last_name,
            $user->first_name,
            $user->email,
            $user->phone_number,
            $user->is_active ? 'Oui' : 'Non',
            $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-',
            $user->created_at->format('d/m/Y'),
        ];
    }
}
