<?php

namespace App\Exports;

use App\Models\FailureRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FailureRecordsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return FailureRecord::with('session')->get();
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'Rake ID',
            'Equipment Name',
            'Fault Name',
            'Classification',
            'Description',
        ];
    }

    public function map($record): array
    {
        return [
            $record->timestamp->format('Y-m-d H:i:s'),
            $record->session->rake_id ?? '',
            $record->equipment_name,
            $record->fault_name,
            $record->classification,
            $record->description,
        ];
    }
}
