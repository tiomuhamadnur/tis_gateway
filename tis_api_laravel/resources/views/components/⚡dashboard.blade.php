<?php

use Livewire\Component;
use App\Models\Session;
use App\Models\FailureRecord;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $totalSessions;
    public $totalRecords;
    public $perRake = [];
    public $perEquipment = [];
    public $perClassification = [];
    public $recentHeavyFaults = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->totalSessions = Session::count();
        $this->totalRecords = FailureRecord::count();

        $this->perRake = Session::select('rake_id', DB::raw('count(*) as count'))
            ->groupBy('rake_id')
            ->get();

        $this->perEquipment = FailureRecord::select('equipment_name', DB::raw('count(*) as count'))
            ->groupBy('equipment_name')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        $this->perClassification = FailureRecord::select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification')
            ->get();

        $this->recentHeavyFaults = FailureRecord::where('classification', 'heavy')
            ->latest('timestamp')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
};
?>

<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Total Sessions</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $totalSessions }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Total Records</h3>
            <p class="text-3xl font-bold text-green-600">{{ $totalRecords }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Active Rakes</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $perRake->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-700">Heavy Faults</h3>
            <p class="text-3xl font-bold text-red-600">{{ $recentHeavyFaults->count() }}</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Per Rake Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Failures per Rake</h3>
            <div id="rake-chart" class="h-64"></div>
        </div>

        <!-- Per Classification Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Failures by Classification</h3>
            <div id="classification-chart" class="h-64"></div>
        </div>
    </div>

    <!-- Recent Heavy Faults -->
    <div class="bg-white p-6 rounded-lg shadow mt-6">
        <h3 class="text-lg font-semibold mb-4">Recent Heavy Faults</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Timestamp</th>
                        <th class="px-4 py-2 text-left">Equipment</th>
                        <th class="px-4 py-2 text-left">Fault</th>
                        <th class="px-4 py-2 text-left">Rake ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentHeavyFaults as $fault)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $fault->timestamp->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2">{{ $fault->equipment_name }}</td>
                        <td class="px-4 py-2">{{ $fault->fault_name }}</td>
                        <td class="px-4 py-2">{{ $fault->session->rake_id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
document.addEventListener('livewire:loaded', () => {
    // Rake Chart
    Highcharts.chart('rake-chart', {
        chart: { type: 'column' },
        title: { text: null },
        xAxis: { categories: @json($perRake->pluck('rake_id')) },
        yAxis: { title: { text: 'Count' } },
        series: [{
            name: 'Failures',
            data: @json($perRake->pluck('count'))
        }]
    });

    // Classification Chart
    Highcharts.chart('classification-chart', {
        chart: { type: 'pie' },
        title: { text: null },
        series: [{
            name: 'Count',
            data: @json($perClassification->map(fn($c) => [$c->classification, $c->count]))
        }]
    });
});
</script>
@endpush