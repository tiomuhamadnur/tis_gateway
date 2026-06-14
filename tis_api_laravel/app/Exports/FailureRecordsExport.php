<?php

namespace App\Exports;

use App\Models\FailureRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FailureRecordsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = FailureRecord::with('session');

        if (!empty($this->filters['classification'])) {
            $query->where('classification', ucfirst(strtolower($this->filters['classification'])));
        }
        if (!empty($this->filters['from']) && !empty($this->filters['to'])) {
            $query->whereBetween('timestamp', [$this->filters['from'] . ' 00:00:00', $this->filters['to'] . ' 23:59:59']);
        }
        if (!empty($this->filters['rake_id'])) {
            $query->whereHas('session', fn($q) => $q->where('rake_id', 'like', '%' . $this->filters['rake_id'] . '%'));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'Rake ID',
            'Equipment Name',
            'Fault Name',
            'Fault Description',
            'Fault Code',
            'Classification',
            'Car No',
            'Speed (km/h)',
            'Overhead (V)',
            'Notch',
            'Duration',
        ];
    }

    public function map($record): array
    {
        return [
            $record->timestamp->format('Y-m-d H:i:s'),
            $record->session->rake_id ?? '',
            $record->equipment_name,
            $record->fault_abbrev,
            $record->fault_description,
            $record->fault_code,
            $record->classification,
            $record->car_no,
            $record->speed_kmh,
            $record->overhead_v,
            $record->notch,
            $record->duration_label,
        ];
    }
}
