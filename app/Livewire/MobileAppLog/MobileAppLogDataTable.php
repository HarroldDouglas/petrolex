<?php

namespace App\Livewire\MobileAppLog;

use App\Models\MobileAppLog;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class MobileAppLogDataTable extends BaseDataTable
{
    protected $model = MobileAppLog::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    private const APP_TYPE_LABELS = [
        'customer_app' => 'App Client',
        'delivery_app' => 'App Livreur',
        'manager_app' => 'App Manager',
    ];

    protected function getExportFileName(): string
    {
        return 'logs-apps-mobiles';
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Date', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y H:i:s')),

            Column::make('Application', 'app_type')
                ->sortable()
                ->format(fn ($value) => self::APP_TYPE_LABELS[$value] ?? $value),

            Column::make('Plateforme', 'platform')
                ->sortable()
                ->format(function ($value, $row) {
                    $version = $row->app_version ? ' v'.$row->app_version : '';

                    return ($value ?? '-').$version;
                }),

            Column::make('Niveau', 'level')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = match ($value) {
                        'error' => 'text-bg-danger',
                        'warning' => 'text-bg-warning',
                        default => 'text-bg-info',
                    };

                    return '<span class="badge '.$badgeClass.'">'.e($value).'</span>';
                })
                ->html(),

            Column::make('Message', 'message')
                ->searchable()
                ->format(function ($value, $row) {
                    return view('partials.mobile-app-logs.message', ['log' => $row]);
                }),

            Column::make('Utilisateur', 'user_id')
                ->sortable()
                ->format(fn ($value, $row) => $row->user?->email ?? '-'),

            Column::make('Appareil', 'device_model')
                ->sortable()
                ->format(function ($value, $row) {
                    $os = $row->os_version ? ' ('.$row->os_version.')' : '';

                    return ($value ?? '-').$os;
                })
                ->deselected(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Application')
                ->options(['' => 'Toutes'] + self::APP_TYPE_LABELS)
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('app_type', $value);
                    }
                }),

            SelectFilter::make('Niveau')
                ->options([
                    '' => 'Tous',
                    'error' => 'Erreur',
                    'warning' => 'Avertissement',
                    'info' => 'Info',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('level', $value);
                    }
                }),

            SelectFilter::make('Plateforme')
                ->options([
                    '' => 'Toutes',
                    'android' => 'Android',
                    'ios' => 'iOS',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('platform', $value);
                    }
                }),
        ];
    }

    public function builder(): Builder
    {
        return MobileAppLog::query()->with('user');
    }
}
