<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Monitor failure records dan statistik sistem</p>
        </div>
    </div>

    {{-- Date Range Filter --}}
    <div
        class="flex flex-wrap items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
        x-data="{
            fp: null,
            dateFrom: '',
            dateTo: '',
            init() {
                this.fp = flatpickr(this.$refs.picker, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    locale: { rangeSeparator: ' → ' },
                    onChange: (dates) => {
                        const fmt = d => {
                            const y = d.getFullYear();
                            const m = String(d.getMonth()+1).padStart(2,'0');
                            const day = String(d.getDate()).padStart(2,'0');
                            return y+'-'+m+'-'+day;
                        };
                        this.dateFrom = dates.length >= 1 ? fmt(dates[0]) : '';
                        this.dateTo   = dates.length >= 2 ? fmt(dates[dates.length-1]) : this.dateFrom;
                    }
                });
            },
            apply() { $wire.call('applyFilter', this.dateFrom, this.dateTo); },
            reset() {
                this.fp.clear();
                this.dateFrom = '';
                this.dateTo = '';
                $wire.call('resetFilter');
            }
        }"
    >
        <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <input
            x-ref="picker"
            type="text"
            readonly
            placeholder="Pilih rentang tanggal..."
            class="flex-1 min-w-[200px] bg-transparent text-sm text-zinc-700 placeholder-zinc-400 focus:outline-none cursor-pointer dark:text-zinc-200 dark:placeholder-zinc-500"
        >
        <button @click="apply()" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition-colors">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Terapkan
        </button>
        <button @click="reset()" class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-50 transition-colors dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-700">
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
<script>
(function () {
    // Use global chart registry so re-execution (SPA navigation) can destroy old instances
    if (!window._tisCharts) window._tisCharts = {};

    // Initial data embedded at render time
    const _initRake  = @json($chartRakeData);
    const _initClass = @json($chartClassData);
    const _initEquip = @json($chartEquipData);

    function renderCharts(data) {
        if (typeof Highcharts === 'undefined' || !document.getElementById('rake-chart')) return;

        const rake  = data ? data.rakeData  : _initRake;
        const cls   = data ? data.classData : _initClass;
        const equip = data ? data.equipData : _initEquip;

        // Destroy any existing instances before re-creating
        ['rake', 'classification', 'equipment'].forEach(function (key) {
            if (window._tisCharts[key]) {
                try { window._tisCharts[key].destroy(); } catch (e) {}
                window._tisCharts[key] = null;
            }
        });

        window._tisCharts.rake = Highcharts.chart('rake-chart', {
            chart: { type: 'column', backgroundColor: 'transparent' },
            title: { text: null },
            xAxis: { categories: rake.map(function(d) { return d.name; }), crosshair: true },
            yAxis: { min: 0, title: { text: 'Failures' }, allowDecimals: false },
            series: [{ name: 'Failures', data: rake.map(function(d) { return d.y; }), color: '#3b82f6' }],
            credits: { enabled: false },
            legend: { enabled: false },
            accessibility: { enabled: false },
        });

        window._tisCharts.classification = Highcharts.chart('classification-chart', {
            chart: { type: 'pie', backgroundColor: 'transparent' },
            title: { text: null },
            series: [{ name: 'Count', colorByPoint: true, data: cls }],
            credits: { enabled: false },
            accessibility: { enabled: false },
        });

        window._tisCharts.equipment = Highcharts.chart('equipment-chart', {
            chart: { type: 'bar', backgroundColor: 'transparent' },
            title: { text: null },
            xAxis: { categories: equip.map(function(d) { return d.name; }) },
            yAxis: { min: 0, title: { text: 'Failures' }, allowDecimals: false },
            series: [{ name: 'Failures', data: equip.map(function(d) { return d.y; }), color: '#8b5cf6' }],
            credits: { enabled: false },
            legend: { enabled: false },
            accessibility: { enabled: false },
        });
    }

    // Expose latest renderCharts globally so the persistent event listener always uses fresh data
    window._tisRenderCharts = renderCharts;

    // Render immediately — handles both first page load (scripts run after DOM ready)
    // and SPA navigation (Livewire injects this script after updating the DOM)
    renderCharts(null);

    // Attach the global dashboardChartsUpdate listener only once across navigations
    if (!window._tisDashboardListenerAttached) {
        window._tisDashboardListenerAttached = true;
        window.addEventListener('dashboardChartsUpdate', function (e) {
            if (document.getElementById('rake-chart')) {
                window._tisRenderCharts(e.detail);
            }
        });
    }
})();
</script>
@endpush
