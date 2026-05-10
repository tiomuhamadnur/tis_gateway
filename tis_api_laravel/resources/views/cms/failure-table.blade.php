<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Failure Records</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Data failure records dari semua sesi</p>
        </div>
        @can('export data')
        <div class="flex gap-2">
            <a href="{{ route('failures.export.excel') }}" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </a>
            <a href="{{ route('failures.export.pdf') }}" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Trainset ID</label>
                <input id="filter-rake" type="text" placeholder="Filter trainset..." class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Classification</label>
                <select id="filter-class" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
                    <option value="">Semua</option>
                    <option value="Heavy">Heavy</option>
                    <option value="Light">Light</option>
                    <option value="Info">Info</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Occurred Dari</label>
                <input id="filter-from" type="date" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Occurred Sampai</label>
                <input id="filter-to" type="date" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button id="btn-filter" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Terapkan Filter
            </button>
            <button id="btn-reset" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300">
                Reset
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="overflow-x-auto p-4">
            <table id="failures-table" class="w-full" style="width:100%">
                <thead>
                    <tr>
                        <th>Occurred At</th>
                        <th>Recovered At</th>
                        <th>Duration</th>
                        <th>Trainset ID</th>
                        <th>Car</th>
                        <th>Equipment</th>
                        <th>Fault</th>
                        <th>Fault Code</th>
                        <th>Classification</th>
                        <th>Notch</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<script defer src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script defer src="https://cdn.datatables.net/2.0.8/js/dataTables.tailwindcss.min.js"></script>
<script>
(function() {
    let table = null;

    function fmtDatetime(d) {
        if (!d) return '-';
        const dt = new Date(d);
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const dd   = dt.getDate().toString().padStart(2,'0');
        const mon  = months[dt.getMonth()];
        const yyyy = dt.getFullYear();
        const hh   = dt.getHours().toString().padStart(2,'0');
        const mm   = dt.getMinutes().toString().padStart(2,'0');
        const ss   = dt.getSeconds().toString().padStart(2,'0');
        return `${dd} ${mon} ${yyyy} ${hh}:${mm}:${ss}`;
    }

    const theadHtml = `<thead><tr>
        <th>Occurred At</th><th>Recovered At</th><th>Duration</th>
        <th>Trainset ID</th><th>Car</th><th>Equipment</th>
        <th>Fault</th><th>Fault Code</th><th>Classification</th><th>Notch</th>
    </tr></thead>`;

    function buildUrl(filters) {
        let url = '{{ route("failures.data") }}';
        let params = new URLSearchParams();
        if (filters.rake_id)        params.append('rake_id', filters.rake_id);
        if (filters.classification) params.append('classification', filters.classification);
        if (filters.from)           params.append('from', filters.from);
        if (filters.to)             params.append('to', filters.to);
        return url + (params.toString() ? '?' + params.toString() : '');
    }

    function initTable(filters) {
        if (table) {
            table.destroy();
            $('#failures-table').empty().append(theadHtml);
        }
        filters = filters || {};

        table = $('#failures-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: buildUrl(filters) },
            columns: [
                {
                    data: 'timestamp', name: 'timestamp',
                    render: d => fmtDatetime(d),
                },
                {
                    data: 'recovered_at', name: 'recovered_at', orderable: false,
                    render: function(d) {
                        if (!d) return '<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700">Still Active</span>';
                        return fmtDatetime(d);
                    }
                },
                {
                    data: 'duration_label', name: 'duration_label', orderable: false, className: 'text-center',
                    render: function(d) {
                        if (!d || d === '-') return '<span class="text-zinc-400">-</span>';
                        return `<span class="font-mono text-xs">${d}</span>`;
                    }
                },
                { data: 'session_rake_id', name: 'session.rake_id' },
                { data: 'car_no', name: 'car_no', className: 'text-center' },
                { data: 'equipment_name', name: 'equipment_name' },
                { data: 'fault_abbrev', name: 'fault_abbrev' },
                { data: 'fault_code', name: 'fault_code', className: 'text-center' },
                {
                    data: 'classification', name: 'classification',
                    render: function(d) {
                        const map = { Heavy:'bg-red-100 text-red-700', Light:'bg-green-100 text-green-700', Info:'bg-blue-100 text-blue-700' };
                        return `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${map[d]||'bg-zinc-100 text-zinc-700'}">${d||'-'}</span>`;
                    }
                },
                { data: 'notch', name: 'notch', className: 'text-center' },
            ],
            language: {
                processing: 'Memuat data...',
                emptyTable: 'Tidak ada data failure record.',
                zeroRecords: 'Tidak ada data yang cocok.',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                search: 'Cari:',
                paginate: { previous: '‹', next: '›' },
            },
            pageLength: 15,
            order: [[0, 'desc']],
        });
    }

    function setup() {
        initTable();

        document.getElementById('btn-filter').addEventListener('click', function() {
            initTable({
                rake_id:        document.getElementById('filter-rake').value,
                classification: document.getElementById('filter-class').value,
                from:           document.getElementById('filter-from').value,
                to:             document.getElementById('filter-to').value,
            });
        });

        document.getElementById('btn-reset').addEventListener('click', function() {
            ['filter-rake','filter-class','filter-from','filter-to'].forEach(id => {
                document.getElementById(id).value = '';
            });
            initTable();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();
</script>
@endpush
