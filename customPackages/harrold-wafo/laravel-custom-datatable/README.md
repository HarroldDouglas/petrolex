# Laravel Custom DataTable

An elegant and powerful DataTable package for Laravel Livewire, built on top of Rappasoft's Laravel Livewire Tables.

## Features

- 🚀 Built on Laravel Livewire Tables
- 📤 Built-in exports (PDF, Excel, CSV)
- 🎨 Theme support with Bootstrap 5
- 🔄 Automatic bulk actions configuration
- 📱 Responsive tables
- 🌍 Internationalization support

## Requirements

- PHP >= 8.1
- Laravel >= 9.0
- Livewire >= 3.0

## Installation

```bash
composer require harrold-wafo/laravel-custom-datatable
```

## Basic Usage

### 1. Create a DataTable Class

```php
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Rappasoft\LaravelLivewireTables\Views\Column;

class UsersTable extends BaseDataTable
{
    protected $model = User::class;
    
    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportClass(): string
    {
        return UsersExport::class;
    }

    protected function getPdfView(): string
    {
        return 'exports.users-pdf';
    }

    protected function getExportFileName(): string
    {
        return 'users';
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable(),
            Column::make('Name', 'name')->sortable()->searchable(),
            Column::make('Email', 'email')->sortable()->searchable(),
            Column::make('Created At', 'created_at')->sortable()
        ];
    }
}
```

### 2. Create an Export Class

```php
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
            'Name',
            'Email',
            'Created At'
        ];
    }

    protected function mapRow($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->email,
            $this->formatDate($row->created_at)
        ];
    }
}
```

### 3. Create a PDF View

```blade
<!-- resources/views/exports/users-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Users Export</title>
</head>
<body>
    <h1>Users List</h1>
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

### 4. Use in Blade

```blade
<div>
    <livewire:users-table />
</div>
```

## Customization

### PDF Options

Override these methods in your table class to customize PDF output:

```php
protected function getPdfFormat(): string
{
    return PdfConstants::FORMAT_A4_PORTRAIT;
}

protected function getPdfMargins(): array
{
    return [
        'margin_top' => 20, // Custom margins
        'margin_right' => PdfConstants::MARGIN_RIGHT,
        'margin_bottom' => PdfConstants::MARGIN_BOTTOM,
        'margin_left' => PdfConstants::MARGIN_LEFT,
    ];
}
```

### Table Configuration

The BaseDataTable provides default settings that can be overridden:

```php
protected const TABLE_WRAPPER_CLASS = 'table-responsive';
protected const TABLE_CLASS = 'table table-striped table-hover';
protected const THEAD_CLASS = 'table-light';
protected const PER_PAGE_OPTIONS = [10, 25, 50, 100];
protected const DEFAULT_PER_PAGE = 10;
```

## Available Constants

### PdfConstants

```php
const FORMAT_A4_LANDSCAPE = 'A4-L';
const FORMAT_A4_PORTRAIT = 'A4';
const MARGIN_TOP = 15;
const MARGIN_RIGHT = 15;
const MARGIN_BOTTOM = 15;
const MARGIN_LEFT = 15;
```

## Publishing Resources

```bash
# Publish everything
php artisan vendor:publish --tag=harroldwafo-laravel-datatable-all

# Publish specific components
php artisan vendor:publish --tag=livewire-tables-config
php artisan vendor:publish --tag=harroldwafo-laravel-datatable-translations
php artisan vendor:publish --tag=harroldwafo-laravel-datatable-views
php artisan vendor:publish --tag=harroldwafo-laravel-datatable-theme-axelit
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.