<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-3 sm:p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Session Downloads</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Search for sessions and download CSV and PDF files from gateway uploads</p>
        </div>
    </div>

    @if(session()->has('success'))
    <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Search</label>
                <input id="filter-search" type="text" placeholder="Session id / rake / file name..." class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Date Range</label>
                <input id="filter-date" type="text" readonly placeholder="Select date range..." class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 cursor-pointer">
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <button id="btn-filter" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Apply Filter
            </button>
            <button id="btn-reset" class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200">
                Reset
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="overflow-x-auto p-2">
            <table id="sessions-table" class="w-full table-compact text-zinc-700 dark:text-zinc-200" style="width:100%">
                <thead class="bg-zinc-50 text-[10px] uppercase tracking-[0.18em] text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                    <tr>
                        <th>Session Date</th>
                        <th>Session Name</th>
                        <th>Trainset</th>
                        <th>Files</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    let table = null;
    let datePicker = null;

    const theadHtml = `<thead><tr>
        <th class="whitespace-nowrap">Session Date</th>
        <th class="whitespace-nowrap">Session Name</th>
        <th class="whitespace-nowrap">Trainset</th>
        <th class="whitespace-nowrap">Files</th>
        <th class="whitespace-nowrap">Actions</th>
    </tr></thead>`;

    function getDateRange() {
        if (!datePicker || !datePicker.selectedDates || datePicker.selectedDates.length === 0) {
            return { from: '', to: '' };
        }
        const fmt = d => {
            const y = d.getFullYear();
            const m = String(d.getMonth()+1).padStart(2,'0');
            const day = String(d.getDate()).padStart(2,'0');
            return y+'-'+m+'-'+day;
        };
        return {
            from: fmt(datePicker.selectedDates[0]),
            to: datePicker.selectedDates.length >= 2 ? fmt(datePicker.selectedDates[datePicker.selectedDates.length-1]) : fmt(datePicker.selectedDates[0]),
        };
    }

    function buildUrl() {
        let url = '{{ route("sessions.data") }}';
        let params = new URLSearchParams();
        const search = document.getElementById('filter-search').value;
        const dr = getDateRange();
        if (search)                 params.append('q', search);
        if (dr.from)                params.append('from', dr.from);
        if (dr.to)                  params.append('to', dr.to);
        return url + (params.toString() ? '?' + params.toString() : '');
    }

    function initTable() {
        if (table) {
            table.destroy();
            $('#sessions-table').empty().append(theadHtml);
        }

        table = $('#sessions-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            responsive: true,
            ajax: { url: buildUrl() },
            columns: [
                { data: 'session_date', name: 'read_time' },
                {
                    data: null, name: 'session_id', orderable: true,
                    render: function(data) {
                        return '<div class="flex flex-col">' +
                            '<span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">' + data.session_id + '</span>' +
                            '<span class="text-xs text-zinc-500 dark:text-zinc-400">' + data.file_count_label + '</span>' +
                            '</div>';
                    }
                },
                { data: 'trainset', name: 'rake_id' },
                { data: 'files', name: 'files', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, className: 'text-right' },
            ],
            language: {
                processing: 'Loading data...',
                emptyTable: 'No session uploads yet.',
                zeroRecords: 'No matching sessions.',
                lengthMenu: 'Show _MENU_ records',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                search: 'Search:',
                paginate: { previous: '\u2039', next: '\u203a' },
            },
            pageLength: 15,
            order: [[0, 'desc']],
        });
    }

    function setup() {
        datePicker = flatpickr(document.getElementById('filter-date'), {
            mode: 'range',
            dateFormat: 'Y-m-d',
            locale: { rangeSeparator: ' \u2192 ' },
        });

        initTable();

        document.getElementById('btn-filter').addEventListener('click', function() {
            initTable();
        });

        document.getElementById('btn-reset').addEventListener('click', function() {
            document.getElementById('filter-search').value = '';
            datePicker.clear();
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
