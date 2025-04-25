<?php

namespace HarroldWafo\LaravelCustomDatatable\DataTables;

use App\Constants\PdfConstants;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Rappasoft\LaravelLivewireTables\DataTableComponent;

abstract class BaseDataTable extends DataTableComponent
{
    protected const EXPORT_FORMATS = [
        'exportPdf' => 'Exporter en PDF',
        'exportExcel' => 'Exporter en Excel',
        'exportCsv' => 'Exporter en CSV'
    ];

    // Define this function when you want export for csv and excel
    protected function getExportClass(): string
    {
        return '';
    }
    // Define this function when you want export for pdf
    protected function getPdfView(): string
    {
        return '';
    }

    // Define this function when you are using export
    protected function getExportFileName(): string
    {
        return 'export';
    }

    // PDF Configuration methods that can be overridden
    protected function getPdfFormat(): string
    {
        return PdfConstants::FORMAT_A4_LANDSCAPE;
    }

    protected function getPdfMargins(): array
    {
        return [
            'margin_top' => PdfConstants::MARGIN_TOP,
            'margin_right' => PdfConstants::MARGIN_RIGHT,
            'margin_bottom' => PdfConstants::MARGIN_BOTTOM,
            'margin_left' => PdfConstants::MARGIN_LEFT,
        ];
    }

    protected function getPdfOptions(): array
    {
        return array_merge(
            $this->getPdfMargins(),
            ['isRemoteEnabled' => true]
        );
    }

    protected const TABLE_WRAPPER_CLASS = 'table-responsive';
    protected const TABLE_CLASS = 'table table-striped table-hover';
    protected const THEAD_CLASS = 'table-light';
    protected const PER_PAGE_OPTIONS = [10, 25, 50, 100];
    protected const DEFAULT_PER_PAGE = 10;

    public bool $rememberColumnSelection = true;
    public bool $rememberFilters = true;
    public bool $rememberSort = true;
    public bool $rememberPerPage = true;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->configureTableAttributes()
            ->configureTableLayout()
            ->configureBulkActions();
    }

    protected function configureTableAttributes(): self
    {
        return $this->setTableWrapperAttributes(['class' => self::TABLE_WRAPPER_CLASS])
            ->setTableAttributes(['class' => self::TABLE_CLASS])
            ->setTheadAttributes(['class' => self::THEAD_CLASS]);
    }

    protected function configureTableLayout(): self
    {
        return $this->setPerPageAccepted(self::PER_PAGE_OPTIONS)
            ->setPerPage(self::DEFAULT_PER_PAGE)
            ->setFilterLayoutSlideDown();
    }

    protected function configureBulkActions(): self
    {
        return $this->setBulkActions(static::EXPORT_FORMATS)
            ->setBulkActionsEnabled();
    }

    protected function getExportQuery(): Builder
    {
        $query = $this->model::whereIn('id', $this->getSelected());

        if ($this->sorts && count($this->sorts) > 0) {
            foreach ($this->sorts as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    protected function handleExport(string $format, callable $exportFunction)
    {
        try {
            $records = $this->getExportQuery()->get();

            if ($records->isEmpty()) {
                $this->notify('Veuillez sélectionner au moins un élément', 'warning');
                return null;
            }

            return $exportFunction($records);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'export {$format} : " . $e->getMessage());
            $this->notify("Une erreur est survenue lors de l'export", 'error');
            return null;
        }
    }

    public function exportPdf()
    {
        return $this->handleExport('PDF', function ($records) {
            $pdf = PDF::loadView($this->getPdfView(), ['data' => $records])
                ->setPaper($this->getPdfFormat())
                ->setOptions($this->getPdfOptions());

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $this->getFormattedFileName('pdf')
            );
        });
    }

    public function exportExcel()
    {
        return $this->handleExport('Excel', function ($records) {
            return Excel::download(
                new ($this->getExportClass())($records->pluck('id')->toArray()),
                $this->getFormattedFileName('xlsx'),
                \Maatwebsite\Excel\Excel::XLSX
            );
        });
    }

    public function exportCsv()
    {
        return $this->handleExport('CSV', function ($records) {
            return Excel::download(
                new ($this->getExportClass())($records->pluck('id')->toArray()),
                $this->getFormattedFileName('csv'),
                \Maatwebsite\Excel\Excel::CSV,
                [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$this->getFormattedFileName('csv')}\""
                ]
            );
        });
    }

    protected function getFormattedFileName(string $extension): string
    {
        return sprintf(
            '%s-%s.%s',
            $this->getExportFileName(),
            now()->format('Y-m-d_H-i-s'),
            $extension
        );
    }

    protected function notify(string $message, string $type = 'info'): void
    {
        $this->dispatch('notify', [
            'message' => $message,
            'type' => $type
        ]);
    }
}
