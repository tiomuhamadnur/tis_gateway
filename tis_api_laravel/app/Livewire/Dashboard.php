<?php

namespace App\Livewire;

use App\Models\FailureRecord;
use App\Models\Session;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public int $totalSessions = 0;
    public int $totalRecords = 0;
    public int $activeRakes = 0;
    public int $heavyFaultCount = 0;
    public array $perRake = [];
    public array $perEquipment = [];
    public array $perClassification = [];
    public array $chartRakeData = [];
    public array $chartClassData = [];
    public array $chartEquipData = [];
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $sessionQ = Session::query();
        $recordQ  = FailureRecord::query();

        if ($this->dateFrom) {
            $sessionQ->whereDate('read_time', '>=', $this->dateFrom);
            $recordQ->whereDate('timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $sessionQ->whereDate('read_time', '<=', $this->dateTo);
            $recordQ->whereDate('timestamp', '<=', $this->dateTo);
        }

        $this->totalSessions = $sessionQ->count();
        $this->totalRecords  = $recordQ->count();

        // Failures per trainset (count actual records, not sessions)
        $rakeQ = FailureRecord::select('failure_sessions.rake_id', DB::raw('count(*) as count'))
            ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
            ->groupBy('failure_sessions.rake_id');

        if ($this->dateFrom) {
            $rakeQ->whereDate('failure_records.timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $rakeQ->whereDate('failure_records.timestamp', '<=', $this->dateTo);
        }

        // Pareto: desc by count, then asc by rake_id (numeric)
        $sorted = $rakeQ->get()->sort(function ($a, $b) {
            if ($b->count !== $a->count) {
                return $b->count - $a->count;
            }
            return (int) $a->rake_id - (int) $b->rake_id;
        })->values();

        $this->activeRakes    = $sorted->count();
        $this->perRake        = $sorted->toArray();
        $this->chartRakeData  = $sorted->map(fn($r) => [
            'name' => 'TS-' . str_pad((int) $r->rake_id, 2, '0', STR_PAD_LEFT),
            'y'    => (int) $r->count,
        ])->values()->toArray();

        $equipQ = FailureRecord::select('equipment_code', 'equipment_name', DB::raw('count(*) as count'))
            ->groupBy('equipment_code', 'equipment_name')
            ->orderByDesc('count')
            ->take(10);

        if ($this->dateFrom) {
            $equipQ->whereDate('timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $equipQ->whereDate('timestamp', '<=', $this->dateTo);
        }

        $equipRows          = $equipQ->get();
        $this->perEquipment = $equipRows->toArray();
        $this->chartEquipData = $equipRows->map(fn($e) => [
            'name' => $e->equipment_name,
            'y'    => (int) $e->count,
        ])->values()->toArray();

        $classQ = FailureRecord::select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification');

        if ($this->dateFrom) {
            $classQ->whereDate('timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $classQ->whereDate('timestamp', '<=', $this->dateTo);
        }

        $classData = $classQ->get();
        $this->perClassification = $classData->toArray();
        $this->heavyFaultCount   = (int) $classData->where('classification', 'Heavy')->sum('count');
        $this->chartClassData    = $classData->map(fn($c) => [
            $c->classification, (int) $c->count,
        ])->values()->toArray();

        $this->dispatch('dashboardChartsUpdate',
            rakeData:  $this->chartRakeData,
            classData: $this->chartClassData,
            equipData: $this->chartEquipData,
        );
    }

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->loadData();
    }

    public function render()
    {
        return view('cms.dashboard');
    }
}
