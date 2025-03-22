@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/css/all.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <style>
        .datatable-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .datatable-header-left {
            display: flex;
            align-items: center;
        }

        .entries-label {
            white-space: nowrap;
        }

        .per-page-select {
            margin: 0 5px;
            width: auto;
            display: inline-block;
        }

        .search-container {
            display: flex;
            align-items: center;
        }

        .search-label {
            margin-right: 5px;
            white-space: nowrap;
        }

        .search-input {
            min-width: 200px;
        }

        .default-data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .default-data-table th {
            padding: 12px 18px;
            border-bottom: 1px solid #e1e1e1;
            font-weight: 500;
            text-align: left;
            position: relative;
            cursor: pointer;
        }

        .default-data-table th i {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
        }

        .default-data-table td {
            padding: 12px 18px;
            border-bottom: 1px solid #f1f1f1;
        }

        .position-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: normal;
            background-color: #f0f8ff;
            color: #505050;
        }

        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .pagination-info {
            text-align: left;
        }

        .pagination-controls {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pagination-controls li {
            margin-right: 5px;
        }

        .pagination-controls li a,
        .pagination-controls li span {
            display: block;
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            text-decoration: none;
        }

        .pagination-controls li.active span {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination-controls li.disabled span {
            color: #6c757d;
            pointer-events: none;
        }

        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #000;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
        }
    </style>
@endpush

<div>
    <div class="datatable-header">
        <div class="datatable-header-left">
            <label class="entries-label">
                Show
                <select wire:model.live="perPage" class="form-select per-page-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                entries
            </label>
        </div>

        <div class="search-container">
            <label class="search-label">Search:</label>
            <input type="search" class="form-control search-input" wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <table class="default-data-table">
        <thead>
            <tr>
                @foreach ($headers as $key => $header)
                    <th wire:click="sort('{{ $key }}')">
                        {{ $header }}
                        @if ($sortField === $key)
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="fas fa-sort text-muted opacity-50"></i>
                        @endif
                    </th>
                @endforeach

                @if (isset($slot) && $slot->isNotEmpty())
                    <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach ($headers as $key => $header)
                        <td>
                            @if ($key == 'position')
                                <span class="position-badge">{!! is_array($row) ? $row[$key] ?? '' : $row->$key ?? '' !!}</span>
                            @else
                                {!! is_array($row) ? $row[$key] ?? '' : $row->$key ?? '' !!}
                            @endif
                        </td>
                    @endforeach

                    @if (isset($slot) && $slot->isNotEmpty())
                        <td>
                            {{ $slot(['row' => $row]) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + (isset($slot) && $slot->isNotEmpty() ? 1 : 0) }}"
                        class="text-center">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($showPagination && method_exists($data, 'links'))
        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() ?? 0 }}
                entries
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
    @endif

    <a href="#" class="back-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
        <i class="fas fa-chevron-up"></i>
    </a>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Show/hide back to top button
            window.addEventListener('scroll', function() {
                const backToTop = document.querySelector('.back-to-top');
                if (window.scrollY > 300) {
                    backToTop.style.display = 'flex';
                } else {
                    backToTop.style.display = 'none';
                }
            });

            // Initialize with back to top hidden
            document.querySelector('.back-to-top').style.display = 'none';

            // Apply custom pagination styling
            customizePagination();

            // Re-apply pagination styling after Livewire updates
            document.addEventListener('livewire:update', function() {
                customizePagination();
            });

            function customizePagination() {
                const paginationElement = document.querySelector('.pagination');
                if (paginationElement) {
                    paginationElement.classList.add('pagination-controls');

                    const pageItems = paginationElement.querySelectorAll('li');
                    pageItems.forEach(item => {
                        // Add necessary classes for styling
                        const link = item.querySelector('a, span');
                        if (link) {
                            if (item.classList.contains('active')) {
                                link.classList.add('active-page');
                            } else if (item.classList.contains('disabled')) {
                                link.classList.add('disabled-page');
                            }
                        }
                    });
                }
            }
        });
    </script>
@endpush
