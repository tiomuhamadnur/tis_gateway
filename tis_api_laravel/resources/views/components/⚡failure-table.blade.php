<?php

use Livewire\Component;
use App\Models\FailureRecord;
use Yajra\DataTables\Facades\DataTables;

new class extends Component
{
    public function getFailuresData()
    {
        return DataTables::of(FailureRecord::with('session'))
            ->addColumn('session_rake_id', function ($record) {
                return $record->session->rake_id;
            })
            ->addColumn('actions', function ($record) {
                return '<button class="text-blue-600 hover:text-blue-900">View</button>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function render()
    {
        return view('livewire.failure-table');
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Failure Records</h1>
        <div class="space-x-2">
            <a href="{{ route('failures.export.excel') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Export Excel
            </a>
            <a href="{{ route('failures.export.pdf') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Export PDF
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table id="failures-table" class="min-w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rake ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fault</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classification</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#failures-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("failures.data") }}',
        columns: [
            { data: 'timestamp', name: 'timestamp' },
            { data: 'session_rake_id', name: 'session.rake_id' },
            { data: 'equipment_name', name: 'equipment_name' },
            { data: 'fault_name', name: 'fault_name' },
            { data: 'classification', name: 'classification' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush

<div>
    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
</div>