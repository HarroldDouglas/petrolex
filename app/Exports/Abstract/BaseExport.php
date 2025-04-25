<?php

namespace App\Exports\Abstract;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

abstract class BaseExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings, WithStyles, ShouldAutoSize
{
    protected array $ids;
    protected string $modelClass;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    abstract protected function getModelClass(): string;
    abstract protected function getHeadings(): array;
    abstract protected function mapRow($row): array;

    public function collection()
    {
        $modelClass = $this->getModelClass();
        return $modelClass::whereIn('id', $this->ids)
            ->orderByRaw("FIELD(id, " . implode(',', $this->ids) . ")")
            ->get();
    }

    public function headings(): array
    {
        return $this->getHeadings();
    }

    public function map($row): array
    {
        return $this->mapRow($row);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => true,
            'include_separator_line' => false,
            'excel_compatibility' => false,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ],
            ],
            'A1:Z1' => [ // Z1 pour couvrir un grand nombre de colonnes
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                    ],
                ],
            ],
        ];
    }

    protected function formatDate($date, string $format = 'd/m/Y'): string
    {
        return $date ? $date->format($format) : '-';
    }

    protected function formatDateTime($date): string
    {
        return $this->formatDate($date, 'd/m/Y H:i');
    }

    protected function formatBoolean($value): string
    {
        return $value ? 'Oui' : 'Non';
    }
}
