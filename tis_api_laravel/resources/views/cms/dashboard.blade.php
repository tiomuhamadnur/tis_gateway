<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Monitor failure records dan statistik sistem</p>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="flex flex-wrap items-end gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div>
            <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Dari Tanggal</label>
            <input wire:model="dateFrom" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Sampai Tanggal</label>
            <input wire:model="dateTo" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
        </div>
        <button wire:click="loadData" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Terapkan Filter
        </button>
        <button wire:click="resetFilter" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-700">
            Reset
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Sessions</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalSessions) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Records</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalRecords) }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Active Trainsets</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $activeRakes }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Heavy Faults</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($heavyFaultCount) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Failures per Trainset</h3>
            <div id="rake-chart" class="h-64"></div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Failures by Classification</h3>
            <div id="classification-chart" class="h-64"></div>
        </div>
    </div>

    {{-- Top Equipment --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <h3 class="mb-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Top 10 Equipment by Failures</h3>
        <div id="equipment-chart" class="h-64"></div>
    </div>
</div>

@push('scripts')
<script defer src="https://code.highcharts.com/highcharts.js"></script>
<script>
(function () {
    // Initial data embedded at render time (used on first load / wire:navigate)
    const _initRake  = @json($chartRakeData);
    const _initClass = @json($chartClassData);
    const _initEquip = @json($chartEquipData);

    // Track instances so we can destroy before re-creating (prevents Highcharts error #16)
    const _charts = {};

    function renderCharts(data) {
        if (typeof Highcharts === 'undefined' || !document.getElementById('rake-chart')) return;

        const rake  = data ? data.rakeData  : _initRake;
        const cls   = data ? data.classData : _initClass;
        const equip = data ? data.equipData : _initEquip;

        ['rake', 'classification', 'equipment'].forEach(function (key) {
            if (_charts[key]) { try { _charts[key].destroy(); } catch (e) {} }
        });

        _charts.rake = Highcharts.chart('rake-chart', {
            chart: { type: 'column', backgroundColor: 'transparent' },
            title: { text: null },
            xAxis: { categories: rake.map(d => d.name), crosshair: true },
            yAxis: { min: 0, title: { text: 'Failures' }, allowDecimals: false },
            series: [{ name: 'Failures', data: rake.map(d => d.y), color: '#3b82f6' }],
            credits: { enabled: false },
            legend: { enabled: false },
            accessibility: { enabled: false },
        });

        _charts.classification = Highcharts.chart('classification-chart', {
            chart: { type: 'pie', backgroundColor: 'transparent' },
            title: { text: null },
            series: [{ name: 'Count', colorByPoint: true, data: cls }],
            credits: { enabled: false },
            accessibility: { enabled: false },
        });

        _charts.equipment = Highcharts.chart('equipment-chart', {
            chart: { type: 'bar', backgroundColor: 'transparent' },
            title: { text: null },
            xAxis: { categories: equip.map(d => d.name) },
            yAxis: { min: 0, title: { text: 'Failures' }, allowDecimals: false },
            series: [{ name: 'Failures', data: equip.map(d => d.y), color: '#8b5cf6' }],
            credits: { enabled: false },
            legend: { enabled: false },
            accessibility: { enabled: false },
        });
    }

    // First render (direct URL access or wire:navigate)
    document.addEventListener('DOMContentLoaded', function () { renderCharts(null); });
    document.addEventListener('livewire:navigated', function () { renderCharts(null); });

    // After Livewire loadData() / resetFilter() — receives fresh data via dispatch()
    window.addEventListener('dashboardChartsUpdate', function (e) { renderCharts(e.detail); });
})();
</script>
@endpush
