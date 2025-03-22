<?php

namespace App\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public $headers = [];
    public $rows = [];
    public $searchable = [];

    public $sortField;
    public $sortDirection = 'asc';
    public $search = '';
    public $perPage = 10;
    public $showSearch = true;
    public $showPagination = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function mount($headers = [], $rows = [], $searchable = [], $sortField = null, $sortDirection = 'asc', $perPage = 10, $showSearch = true, $showPagination = true)
    {
        $this->headers = $headers;
        $this->rows = $rows;
        $this->searchable = count($searchable) ? $searchable : array_keys($headers);
        $this->sortField = $sortField ?: array_key_first($headers);
        $this->sortDirection = $sortDirection;
        $this->perPage = $perPage;
        $this->showSearch = $showSearch;
        $this->showPagination = $showPagination;
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function processData()
    {
        $data = collect($this->rows);

        if (! empty($this->search)) {
            $data = $data->filter(function ($row) {
                foreach ($this->searchable as $field) {
                    $value = is_array($row) ? ($row[$field] ?? '') : ($row->$field ?? '');
                    if (stripos($value, $this->search) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        if ($this->sortField) {
            $data = $data->sortBy(function ($row) {
                return is_array($row) ? ($row[$this->sortField] ?? '') : ($row->{$this->sortField} ?? '');
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        if ($this->showPagination) {
            return $this->paginateCollection($data, $this->perPage);
        }

        return $data;
    }

    protected function paginateCollection(Collection $collection, $perPage)
    {
        $page = request()->query('page', 1);

        $items = $collection->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function render()
    {
        $data = $this->processData();

        return view('livewire.data-table', [
            'data' => $data,
        ]);
    }
}
