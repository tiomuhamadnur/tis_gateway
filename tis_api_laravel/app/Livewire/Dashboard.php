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
    public array $perCar = [];
    public array $chartRakeData = [];
    public array $chartClassData = [];
    public array $chartEquipData = [];
    public array $chartCarData = [];

    // Internal filter state (set by applyFilter / resetFilter)
    private string $dateFrom = '';
    private string $dateTo = '';
    private ?int $selectedRake = null;
    private ?int $selectedCar = null;
    private ?string $selectedClassification = null;
    private ?string $selectedEquipment = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function applyFilter(string $from, string $to): void
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
        $this->loadData();
    }

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->selectedRake = null;
        $this->selectedCar = null;
        $this->selectedClassification = null;
        $this->selectedEquipment = null;
        $this->loadData();
    }

    public function filterByRake(int $rakeId): void
    {
        $this->selectedRake = $rakeId;
        $this->loadData();
    }

    public function filterByCar(int $carNo): void
    {
        $this->selectedCar = $carNo;
        $this->loadData();
    }

    public function filterByClassification(string $classification): void
    {
        $this->selectedClassification = $classification;
        $this->loadData();
    }

    public function filterByEquipment(string $equipmentCode): void
    {
        $this->selectedEquipment = $equipmentCode;
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

        // Failures per trainset
        $rakeQ = FailureRecord::select('failure_sessions.rake_id', DB::raw('count(*) as count'))
            ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
            ->groupBy('failure_sessions.rake_id');

        if ($this->dateFrom) {
            $rakeQ->whereDate('failure_records.timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $rakeQ->whereDate('failure_records.timestamp', '<=', $this->dateTo);
        }
        if ($this->selectedCar) {
            $rakeQ->where('failure_records.car_no', $this->selectedCar);
        }
        if ($this->selectedClassification) {
            $rakeQ->where('failure_records.classification', $this->selectedClassification);
        }
        if ($this->selectedEquipment) {
            $rakeQ->where('failure_records.equipment_code', $this->selectedEquipment);
        }

        $sorted = $rakeQ->get()->sort(function ($a, $b) {
            if ($b->count !== $a->count) {
                return $b->count - $a->count;
            }
            return (int) $a->rake_id - (int) $b->rake_id;
        })->values();

        $this->activeRakes   = $sorted->count();
        $this->perRake       = $sorted->toArray();
        $this->chartRakeData = $sorted->map(fn($r) => [
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
        if ($this->selectedRake) {
            $equipQ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
                   ->where('failure_sessions.rake_id', $this->selectedRake);
        }
        if ($this->selectedCar) {
            $equipQ->where('car_no', $this->selectedCar);
        }
        if ($this->selectedClassification) {
            $equipQ->where('classification', $this->selectedClassification);
        }

        $equipRows            = $equipQ->get();
        $this->perEquipment   = $equipRows->toArray();
        $this->chartEquipData = $equipRows->map(fn($e) => [
            'name' => $e->equipment_name,
            'y'    => (int) $e->count,
        ])->values()->toArray();

        // Failures per car
        $carQ = FailureRecord::select('car_no', DB::raw('count(*) as count'))
            ->groupBy('car_no')
            ->orderByDesc('count');

        if ($this->dateFrom) {
            $carQ->whereDate('timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $carQ->whereDate('timestamp', '<=', $this->dateTo);
        }
        if ($this->selectedRake) {
            $carQ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
                 ->where('failure_sessions.rake_id', $this->selectedRake);
        }
        if ($this->selectedClassification) {
            $carQ->where('classification', $this->selectedClassification);
        }
        if ($this->selectedEquipment) {
            $carQ->where('equipment_code', $this->selectedEquipment);
        }

        $carRows            = $carQ->get();
        $this->perCar       = $carRows->toArray();
        $this->chartCarData = $carRows->map(fn($c) => [
            'name' => 'Car ' . $c->car_no,
            'y'    => (int) $c->count,
        ])->values()->toArray();

        $classQ = FailureRecord::select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification');

        if ($this->dateFrom) {
            $classQ->whereDate('timestamp', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $classQ->whereDate('timestamp', '<=', $this->dateTo);
        }
        if ($this->selectedRake) {
            $classQ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
                   ->where('failure_sessions.rake_id', $this->selectedRake);
        }
        if ($this->selectedCar) {
            $classQ->where('car_no', $this->selectedCar);
        }
        if ($this->selectedEquipment) {
            $classQ->where('equipment_code', $this->selectedEquipment);
        }

        $classData               = $classQ->get();
        $this->perClassification = $classData->toArray();
        $this->heavyFaultCount   = (int) $classData->where('classification', 'Heavy')->sum('count');
        $this->chartClassData    = $classData->map(fn($c) => [
            $c->classification, (int) $c->count,
        ])->values()->toArray();

        $this->dispatch('dashboardChartsUpdate',
            rakeData:  $this->chartRakeData,
            classData: $this->chartClassData,
            equipData: $this->chartEquipData,
            carData:   $this->chartCarData,
        );
    }

    public function render()
    {
        return view('cms.dashboard');
    }
}
