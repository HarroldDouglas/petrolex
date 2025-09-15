<?php

namespace App\Livewire\User;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;

class DeliveryPersonOrderDataTable extends BaseDataTable
{
    protected $model = Order::class;
    public User $user;

    protected const DEFAULT_SORT_FIELD = 'order_date';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'commandes_livreur_'.$this->user->id;
    }

    public function builder(): Builder
    {
        return Order::query()
            ->where('delivery_person_id', $this->user->deliveryPerson->id)
            ->with(['customer.user', 'distributionCenter', 'payment']);
    }

    public function columns(): array
    {
        return [
            Column::make('N° commande', 'order_number')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<a href="'.route('orders.details', $row->id).'" class="btn-link">'.e($value).'</a>'
                    );
                }),

            Column::make('Client', 'customer_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('customer.user', function (Builder $q) use ($searchTerm) {
                        $q->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where('first_name', 'like', '%'.$searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$searchTerm.'%']);
                        });
                    });
                })
                ->format(function ($value, $row) {
                    $user = optional(optional($row->customer)->user);
                    if (! $user->first_name && ! $user->last_name) {
                        return '-';
                    }

                    return new HtmlString(
                        '<a href="'.route('users.customer.details', $user->id).'" class="btn-link fw-bold">'.trim(($user->first_name ?? '').' '.($user->last_name ?? '')).'</a>'
                    );
                }),

            Column::make('Total', 'total_amount')
                ->sortable()
                ->format(fn ($value) => Currency::from(config('countries.default_currency', 'XAF'))->format($value)),

            Column::make('Date', 'order_date')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('Statut', 'status')
                ->sortable()
                ->format(function (OrderStatus $value) {
                    return new HtmlString(
                        '<span class="badge '.$value->getBadgeClass().'">'.e($value->label).'</span>'
                    );
                }),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<a href="'.route('orders.details', $row->id).'" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Détails</a>'
                    );
                }),
        ];
    }
}
